<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Training;
use App\Form\TrainingType;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Handler\TrainingHandler;
use App\Service\TrainingService;
use App\Handler\EnrollmentHandler;
use App\Repository\UserRepository;
use App\Service\NotificationService;

use App\Event\TrainingEnrollmentEvent;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Workflow\Registry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Message\TrainingEnrollmentNotification;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Route to handle all training-related actions
#[Route('/training')]
final class TrainingController extends AbstractController
{
    // Constructor dependency injection for services and components
    public function __construct(
        private NotificationService $notificationService, 
        private TrainingService $trainingService, 
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager,
        private TrainingRepository $trainingRepository,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
        private WorkflowInterface $trainingWorkflow,
        private SluggerInterface $slugger,
        private TrainingHandler $trainingHandler,
        private EnrollmentHandler $enrollmentHandler,
        private Registry $workflowRegistry)
    {}

    // Route to display all training courses, with pagination
    #[Route('/', name: 'app_training')]
    public function index(Request $request, Security $security): Response
    {
        try {
            // Fetch the logged-in user
            $user = $security->getUser();

            // Fetch all or confirmed trainings based on user role (admin vs regular user)
            $trainings = ($user && in_array('ROLE_ADMIN', $user->getRoles())) 
                ? $this->trainingService->getAllTrainings() 
                : $this->trainingService->getAllTrainingsConfirmed();

            $totalTrainings = count($trainings);

            // Paginate the trainings data for display
            $pagination = $this->paginator->paginate(
                $trainings,
                $request->query->getInt('page', 1), 
                6
            );

        } catch (\Exception $e) {
            // Handle error if fetching trainings fails
            $this->addFlash('error', 'An error occurred while fetching trainings.');
            return $this->redirectToRoute('app_home'); 
        }

        // Render the training list page
        return $this->render('training/index.html.twig', [
            'trainings' => $pagination,
            'totalTrainings' => $totalTrainings,
        ]);
    }

    // API route to get all trainings in JSON format
    #[Route('/getTrainingsAPI', name: 'trainings_api')]
    public function getTrainingsAPI()
    {
        try {
            // Fetch trainings from the repository
            $trainings = $this->trainingRepository->findBy([], ["title" => "ASC"]);
            // Serialize the data into JSON format
            $jsonContent = $this->serializer->serialize($trainings, 'json', ['groups' => ['training_detail']]);
            // Return JSON response
            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            // Return error response if an issue occurs
            return new JsonResponse(['error' => 'Failed to fetch trainings.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Route to create a new training
    #[Route('/new', name: 'new_training')]
    public function new(Request $request): Response
    {
        // Create and handle form submission for new training
        $form = $this->createForm(TrainingType::class, new Training());
        $form->handleRequest($request);

        // Process form submission if valid
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $training = $form->getData();

                // Generate a slug for the training
                $slug = $this->slugger->slug($training->getTitle());
                $training->setSlug($slug);

                // Persist the training entity and save it to the database
                $this->entityManager->persist($training);
                $this->entityManager->flush();

                // Show success message and redirect
                $this->addFlash('success', 'Training successfully created.');
                return $this->redirectToRoute('app_training');
            } catch (ORMException $e) {
                // Handle ORM exception (e.g., database issues)
                $this->addFlash('error', 'Failed to save the training.');
            } catch (\Exception $e) {
                // Catch any other exception
                $this->addFlash('error', 'An unexpected error occurred.');
            }
        }

        // Render the form page for creating a new training
        return $this->render('training/new.html.twig', [
            'formAddTraining' => $form,
        ]);
    }

    // Route to show details of a single training
    #[Route('/{slug}', name: 'show_training')]
    public function show(string $slug): Response
    {
        try {
            // Fetch the training by its slug
            $training = $this->trainingService->getTrainingBySlug($slug);

            // If training is not found, throw exception
            if (!$training) {
                throw new NotFoundHttpException('Training not found.');
            }

            $user = $this->getUser();

            $isEnrolled = $user ? $this->trainingService->isUserEnrolled($training, $user) : false;
            $coursesNotInTraining = $this->trainingService->getCoursesNotInTraining($training);
            $trainees = $this->userRepository->getTraineesSortedByLastName($training);

        } catch (NotFoundHttpException $e) {
            // Handle training not found exception
            $this->addFlash('error', 'Training not found.');
            return $this->redirectToRoute('app_training');
        } catch (\Exception $e) {
            // Handle other types of errors
            $this->addFlash('error', 'An error occurred while fetching the training details.');
            return $this->redirectToRoute('app_training');
        }

        // Render the page with training details
        return $this->render('training/show.html.twig', [
            'training' => $training,
            'isEnrolled' => $isEnrolled,
            'coursesNotInTraining' => $coursesNotInTraining,
            'trainees' => $trainees,
        ]);
    }

    // Route to toggle a user's enrollment status for a training
    #[Route('/{id}/enroll', name: 'enroll_training')]
    public function toggleEnrollment(Training $training): Response
    {
        try {
            // Get the currently logged-in user
            $user = $this->getUser();

            // Check if the user is allowed to enroll (role check)
            if (!$user || !in_array('ROLE_STUDENT', $user->getRoles())) {
                throw new \Exception('You do not have permission to enroll in this training.');
            }

            // Toggle the enrollment status
            $isEnrolled = !$training->getTrainees()->contains($user);

            if (!$isEnrolled) {
                // Unenroll the user from the training
                $training->removeTrainee($user);
                $this->notificationService->createNotification('You have successfully unenrolled from the training: ' . $training->getTitle(), $user);
            } else {
                // Enroll the user in the training
                $training->addTrainee($user);
                $this->notificationService->createNotification('You are now enrolled in this training: ' . $training->getTitle(), $user);

                // Dispatch enrollment notification (currently commented out)
                // $this->bus->dispatch(new TrainingEnrollmentNotification(
                //     $user->getEmail(),
                //     $training->getTitle()
                // ));
            }

            // Persist the training entity and save changes
            $this->entityManager->persist($training);
            $this->entityManager->flush();

            // Dispatch event based on enrollment status
            $this->dispatcher->dispatch(
                new TrainingEnrollmentEvent($user, $training, $isEnrolled), 
                $isEnrolled ? TrainingEnrollmentEvent::ENROLLED : TrainingEnrollmentEvent::UNENROLLED
            );

            // Show success message
            $this->addFlash('success', 'Your enrollment status has been updated.');
        } catch (\Exception $e) {
            // Handle error and log exception
            $this->addFlash('error', 'Failed to update enrollment status.');
            $this->logger->error('Error: ' . $e->getMessage());
        }

        // Redirect back to training details page
        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }

    #[Route('/{training}/{trainee}/unenroll', name: 'unenroll_training')]
    #[IsGranted("ROLE_ADMIN")]
    public function unenroll(Training $training, User $trainee): Response
    {
        try {
            // Get the currently logged-in user
            $user = $this->getUser();

            // Check if the user is allowed to unenroll (role check)
            if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
                throw new \Exception('You do not have permission to unenroll student from this training.');
            }

            // Unenroll the user from the training
            $training->removeTrainee($trainee);
            $this->notificationService->createNotification('You have successfully unenrolled student from the training: ' . $training->getTitle(), $user);

            // Persist the training entity and save changes
            $this->entityManager->persist($training);
            $this->entityManager->flush();

            // Show success message
            $this->addFlash('success', 'You have unenrolled this student from the training.');
        } catch (\Exception $e) {
            // Handle error and log exception
            $this->addFlash('error', 'Failed to unenroll from training.');
            $this->logger->error('Error: ' . $e->getMessage());
        }

        // Redirect back to training details page
        return $this->redirectToRoute('app_student_show', ['id' => $trainee->getId()]);
    }


    // Route to move a training to the "review" stage
    #[Route('/{id}/to-review', name: 'training_to_review')]
    #[IsGranted("ROLE_ADMIN")]
    public function toReview(Training $training): Response
    {
        try {

            $workflow = $this->workflowRegistry->get($training, 'training_workflow');

            // Check if the transition to review is possible
            if ($workflow->can($training, Training::TRANSITION_TO_REVIEW)) {
                $workflow->apply($training, Training::TRANSITION_TO_REVIEW);
                $this->entityManager->flush();
                $this->addFlash('success', 'Training moved to review.');
            } else {
                $this->addFlash('error', 'This training cannot be moved to review.');
            }

            // Redirect back to the training details page
            return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
        } catch (\Exception $e) {
            // Log error and show error message
            // $this->get('logger')->error('Error reviewing training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while reviewing the training.');
        }

        // Redirect back to the training details page
        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }
    
    // Route to confirm a training
    #[Route('/{id}/to-confirmed', name: 'training_to_confirmed')]
    #[IsGranted("ROLE_ADMIN")]
    public function toConfirmed(Training $training): Response
    {
        $user = $this->getUser();

        try {

            $workflow = $this->workflowRegistry->get($training, 'training_workflow');

            // Check if the transition to confirmed is possible
            if ($workflow->can($training, Training::TRANSITION_TO_CONFIRMED)) {
                $workflow->apply($training, Training::TRANSITION_TO_CONFIRMED);
                $this->entityManager->flush();

                // Notify user about the confirmation
                $this->notificationService->createNotification('You have successfully confirmed this training: ' . $training->getTitle(), $user);

                // Show success message and redirect
                $this->addFlash('success', 'Training confirmed.');
                return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
            } else {
                $this->addFlash('error', 'This training cannot be confirmed.');
            }
        } catch (\Exception $e) {
            // Log error and show error message
            // $this->get('logger')->error('Error confirming training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while confirming the training.');
        }

        // Redirect back to the training details page
        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }

    // Route to move a training back to draft state
    #[Route('/{id}/to-draft', name: 'training_to_draft')]
    #[IsGranted("ROLE_ADMIN")]
    public function toDraft(Training $training): Response
    {
        try {

            $workflow = $this->workflowRegistry->get($training, 'training_workflow');

            // Check if the transition to draft is possible
            if ($workflow->can($training, Training::TRANSITION_TO_DRAFT)) {
                $workflow->apply($training, Training::TRANSITION_TO_DRAFT);
                $this->entityManager->flush();
                $this->addFlash('success', 'Training reverted to draft.');
            } else {
                $this->addFlash('error', 'This training cannot be reverted to draft.');
            }
        } catch (\Exception $e) {
            // Log error and show error message
            // $this->get('logger')->error('Error drafting training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while drafting the training.');
        }

        // Redirect back to the training details page
        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }
}