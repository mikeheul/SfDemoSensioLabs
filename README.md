# Platform Training (SfDemoSensioLabs)

This project is a training platform that allows users to enroll in courses, manage their training, and receive notifications. Administrators can manage the entire CRUD for Training, Course, and User entities through EasyAdmin. The platform also exposes certain information via API Platform and allows advanced user management through Symfony's security and authorization mechanisms.

## Features

### Training Platform
- Trainings and Courses: Users can view and enroll in trainings, which contain a collection of courses. Each training has a title, description, price, start date, and end date. Each course has a name and description.
- User Enrollment: Users can enroll in specific trainings. After enrollment, an email notification is sent to the user via an EventSubscriber.
- Admin CRUD (via EasyAdmin): The admin can manage Training, Course, and User entities with full CRUD functionality using EasyAdmin.
- Training Pagination: The list of trainings is paginated to avoid long lists using KnpPaginator.

### Custom Commands
- Deactivate User Command: A custom Symfony command (app:deactivate-user) allows administrators to deactivate a user by email.
- Export Users: A custom command is available to export the list of users.
- Create Custom Services: Custom services can be created using Symfony commands.

### API Endpoints (API Platform)
API Platform: Some of the project’s data (such as Training and Course) are exposed via API Platform. This allows external applications or users to interact with the platform programmatically.

### Notifications
- Notification System: Users are notified when they successfully enroll or unenroll from a training session.
- Email Notifications: The system sends emails to users upon successful enrollment or unsubscription.
- Deactivation Notification: Users receive notifications when their account is deactivated.
- EventSubscriber: Used to listen for events like user registration, course enrollment, and account deactivation to trigger notifications.

### User Management & Security (Voters)
- Voters: Access control is managed through Voters for fine-grained control over user permissions. This ensures that only authorized users (e.g., ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT) can perform specific actions. (For example, a teacher or an admin can show students enrolled in a specific training / Only students are allowed to enroll in a specific training / Only admin can create trainings or courses)
- Profile: The user profile page allows users to view the trainings they have enrolled in.

### Fixtures
Fixtures: A script has been created to populate the database with test data (trainings, courses, users) to facilitate development and testing.

### Testing
- The project uses PHPUnit for unit and functional tests.