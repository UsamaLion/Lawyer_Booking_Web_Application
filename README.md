# Lawyer Booking Web Application

A web-based application that allows users to book consultations with lawyers. This application provides a platform where users can search for lawyers based on their expertise, availability, and location. It offers an easy-to-use interface for clients to book appointments and manage their bookings efficiently. Lawyers can create detailed profiles, manage appointments, and track their availability.

## Features

- **User Registration & Authentication**: Allows users to create accounts, log in, and manage their profiles.
- **Lawyer Registration**: Comprehensive lawyer registration process with detailed information and document uploads.
- **Lawyer Search**: Search and filter lawyers based on practice areas, location, and availability.
- **Appointment Booking**: Clients can book consultations with lawyers, view available slots, and schedule appointments.
- **Admin Panel**: Admins can manage user accounts, lawyer profiles, and appointments.
- **Responsive Design**: Fully responsive web application that works across various devices.
- **Notifications**: In-app and email notifications for users and lawyers about appointments, reviews, and status changes.
- **Role-based Access**: Different dashboards and functionalities for regular users, lawyers, and admins.
- **Review System**: Clients can rate and review lawyers after consultations.

## Recent Updates

- Enhanced lawyer registration process with additional fields and document uploads.
- Implemented a comprehensive notification system for various events.
- Added role-based redirection after login for improved user experience.
- Updated database schema to accommodate new lawyer profile information.
- Improved file upload functionality with unique filename generation.

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Version Control**: Git (hosted on GitHub)

## Installation

To get a local copy up and running, follow these simple steps.

### Prerequisites

- [PHP](https://www.php.net/) version 7 or higher
- [MySQL](https://www.mysql.com/) database server
- [XAMPP](https://www.apachefriends.org/index.html) (for local server setup) or any other server of your choice

### Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/UsamaLion/Lawyer_Booking_Web_Application.git
   ```

2. Navigate to the project directory:
   ```bash
   cd Lawyer_Booking_Web_Application
   ```

3. Set up the database:
   - Import the provided database schema (`database.sql`) into your MySQL server using PHPMyAdmin or any other database management tool.
   - Update the database configuration in the project (typically in `config.php` or `.env` file) with your MySQL credentials.

4. Install dependencies (if applicable):
   - For PHP/Laravel projects, run `composer install` to install the dependencies.
   - For frontend dependencies (React or others), run `npm install` if using Node.js.

5. Run the application:
   - If using XAMPP, place the project folder in the `htdocs` directory and navigate to `http://localhost/your_project_name` in your browser.

6. Configure email settings (optional for email notifications) by modifying the `config/email.php` or `.env` file.

## Usage

- **User**: Users can register and log in to the platform. After logging in, they can search for lawyers, view profiles, and book consultations based on availability.
- **Lawyer**: Lawyers can create their profiles, set their availability, and view scheduled consultations.
- **Admin**: Admins have access to manage all users, lawyers, and appointments from the admin panel.

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Make your changes.
4. Commit your changes (`git commit -m 'Add new feature'`).
5. Push to the branch (`git push origin feature/your-feature`).
6. Open a Pull Request.

## License

This project is licensed under the MIT License
