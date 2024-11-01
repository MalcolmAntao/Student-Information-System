# Student Information System

This project is a Student Information System developed to manage and display student-related data efficiently. Built using PHP with PDO and MySQL, this system supports features such as user authentication, dynamic charting for performance metrics, course enrollment, and a comprehensive role-based user structure.

## Table of Contents
- [Student Information System](#student-information-system)
- [Table of Contents](#table-of-contents)
- [Features](#features)
- [Database Structure](#database-structure)
  - [Tables](#tables)
  - [Triggers](#triggers)
  - [Views](#views)
- [Installation](#installation)
- [User Roles and Functionalities](#user-roles-and-functionalities)
  - [1. Students](#1-students)
  - [2. Instructors](#2-instructors)
  - [3. Administrators](#3-administrators)
  - [4. System Users (General)](#4-system-users-general)
- [Contributors](#contributors)
- [Contact](#contact)


## Features

- **User Authentication**: Sessions are managed using `Session.php`, ensuring secure login and access control for students and admins.
- **Database Connectivity**: Uses `Connection.php` for secure and reliable database connections with prepared statements.
- **Course Enrollment**: Fetches and displays available courses by category (core, electives, major/minor) based on department and semester.
- **Performance Metrics**: Dynamic SGPA and CGPA calculations, displayed through interactive radar and doughnut charts.
- **Course Management**: Allows adding, updating, and deleting courses. Enforces enrollment limits based on course category.
- **Light/Dark Mode**: Adaptive UI that changes based on user preference.
- **Smooth Loading**: Enhances user experience by ensuring smooth page transitions on reload.

## Database Structure

This project includes multiple tables, triggers and views for managing student, course, and role information.

### Tables

- **announcements**: Stores announcements made by teachers or administrators, including title, content, posting date, and author.

- **courses**: Holds information about each course, including course code, name, description, credits, and the department and semester it is offered.

- **course_selections**: Manages the courses students select based on available enrollments and course categories.

- **departments**: Lists the departments in the institution, each with a unique ID and name.

- **enrollment_types**: Categorizes different types of enrollments, such as core or elective courses.

- **enrolls_in**: Tracks student enrollments in specific courses, including enrollment year and semester details.

- **gender_definitions**: Stores gender definitions for student and faculty profiles.

- **grades**: Manages student grades for each course, linking to both the student and course information by semester and academic year.

- **instructors**: Contains instructor information, including name and contact details.

- **instructor_assignments**: Maps instructors to their course assignments.

- **mentorship**: Manages instructor-student mentorship assignments by semester, connecting students to faculty mentors within departments.

- **quotes**: Stores motivational or informational quotes, often used in notifications or dashboard messages.

- **roles**: Defines different user roles within the system, such as student, teacher, or admin.

- **role_associations**: Associates users with specific roles, enabling role-based access within the application.

- **students**: Stores key student information such as ID, name, roll number, university number, and current semester.

- **teaches**: Links instructors to the courses they teach.

- **users**: Manages all system users, including their login credentials and roles.
  

### Triggers

- **after_course_insert**: Automatically enrolls students into a core course when a new core course is added. This inserts enrollment records for all students in the same department and semester as the course.

- **after_course_acceptance**: Once a course selection is accepted, this trigger enrolls the student into the course by adding an entry in the `Enrolls_In` table with the current year.

- **update_instructor_email**: Updates the email in the `users` table whenever an instructor's contact information is updated.

- **auto_enroll_core_courses**: Automatically enrolls new students into core courses available in their department and semester upon student insertion.

- **auto_enroll_core_courses_after_update**: Enrolls students into core courses if their department or semester is updated, ensuring they are matched with the correct department and semester core courses.

- **update_student_email**: Updates the email in the `users` table whenever a student's email is modified.

### Views
  
- **student_sgpa_cgpa**: Stores semester grade point averages (SGPA) and cumulative grade point averages (CGPA) for students


## Installation

1. Clone this repository:  
   ```bash
   git clone https://github.com/MalcolmAntao/student-information-system.git
   ```
2. Import the studentdb SQL schema into your MySQL database.
3. Configure the Connection.php file with your database credentials.
4. Start your local server and navigate to the project directory.

## User Roles and Functionalities

This section describes the various user roles within the database system and their associated functionalities.

## 1. Students

### Usage
- **Login**: Students log in using their credentials to access their dashboard.
- **View Grades**: Students can view their grades for courses enrolled in by accessing the "Grades" section.
- **Select Courses**: Students can select courses to enroll in based on their available options in the "Course Selections" section.
- **Check Announcements**: Students can view announcements posted by instructors or administrators for important updates.
- **Access Academic Performance**: Students can check their semester grade point averages (SGPA) and cumulative grade point averages (CGPA).

### Permissions
- Add or drop courses based on their enrollment type.
- Access personal information and grades.
- Update their own profile information.

## 2. Instructors

### Usage
- **Login**: Teachers (instructors) log in to manage their courses and students.
- **Post Announcements**: Teachers can create and post announcements to inform students about important events or updates.
- **Manage Course Assignments**: Teachers can assign themselves to courses they will be teaching and update course materials as necessary.
- **View Student Grades**: Teachers can view the grades of students enrolled in their courses and update grades as required.
- **Mentor Students**: Teachers can manage mentorship relationships with students, providing guidance and support.

### Permissions
- Create, read, update, and delete announcements.
- Manage grades for students in their courses.
- Access student performance metrics and course selections.

## 3. Administrators

### Usage
- **Login**: Administrators log in to manage the overall system and user accounts.
- **Manage Users**: Administrators can create, update, and delete user accounts for students, teachers, and other admins.
- **Oversee Courses**: Administrators can add, modify, or delete courses and manage course offerings across different departments.
- **View Reports**: Administrators can generate reports on student performance, enrollment statistics, and other metrics to assess the institution's academic standing.

### Permissions
- Full access to all tables and records in the database.
- Create and manage all types of users, including role assignments.
- Manage system settings and configurations.

## 4. System Users (General)

### Usage
- **Login**: All users log in with their specific roles and credentials to access the system.
- **Profile Management**: Users can update their profile information, including contact details and passwords.
- **Role-Based Access**: Users experience different functionalities based on their roles, ensuring security and appropriate access levels.

### Permissions
- Limited access to functionalities based on the assigned role.
- Ability to view and interact with data relevant to their responsibilities.


## Contributors 
- [P Jayesh Naidu](https://github.com/25Jayesh10) 
- [Nathania Baptista](https://github.com/nathaniabaptista) 

## Contact

If you have any questions, feedback, or suggestions regarding the Student Information System project, please feel free to reach out to us:

- **Malcolm Antão**  
  Email: [malcolmantao164@gmail.com](mailto:malcolmantao164@gmail.com)  
  
- **P Jayesh Naidu**  
  GitHub: [25Jayesh10](https://github.com/25Jayesh10)

- **Nathania Baptista**  
  GitHub: [nathaniabaptista](https://github.com/nathaniabaptista)
  
We welcome contributions and appreciate your interest in improving the system!

