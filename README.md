# 🎓 Campus Placement Hub (PHP + MySQL)

The Campus Placement Hub is a comprehensive web-based application designed to digitize, streamline, and manage the entire campus recruitment lifecycle. Built using core PHP, MySQL, HTML, CSS, and JavaScript, this project provides a centralized platform for placement administrators to manage student data, company profiles, and job postings, while offering students a personalized portal to discover opportunities and track their placement journey.

---

## 🚀 Core Features

This project features two distinct, role-based portals with a rich set of functionalities.

### 🧑‍💼 Admin Portal
The Admin Portal is the command center for the placement officer, providing full control over the entire system.

- **[✔] Dynamic Dashboard:** At-a-glance statistics on total students, placements, and registered companies.
- **[✔] Student Management:** Full CRUD (Create, Read, Update, Delete) capabilities for all student profiles, including personal and academic details.
- **[✔] Company Management:** A dedicated module to add, edit, and delete company profiles.
- **[✔] Job Posting Management:** Create and manage detailed job postings with specific eligibility criteria (CGPA, backlogs) and link them to companies.
- **[✔] Placement Tracking:** A complete system to officially record which student has been placed in which job, which automatically updates the student's status to "Placed".
- **[✔] Application Management:** View a list of all students who have applied for a specific job and update their application status (e.g., "Shortlisted", "Interviewing", "Rejected").
- **[✔] Powerful Search & Filtering:**
    - A "Quick Find" tool on the dashboard to instantly access a student's profile.
    - An "Advanced Search" engine to filter students based on academic performance and placement status.
- **[✔] Secure Authentication:** A dedicated login system for staff.

### 🧑‍🎓 Student Portal
The Student Portal provides a personalized and interactive experience for students.

- **[✔] Secure Authentication:** Secure login, logout, and password change functionality.
- **[✔] Personalized Dashboard:** Students can view their complete, up-to-date profile, including personal details, academic scores, and current placement status.
- **[✔] Job Eligibility Engine:** The "magic" feature of the portal. It automatically displays a personalized list of all job postings a student is eligible for based on their CGPA and backlogs.
- **[✔] Job Application System:** Students can apply for eligible jobs with a single click. The UI provides clear feedback, showing which jobs they have already applied for.
- **[✔] Application Status Tracking:** A dedicated "My Applications" page where students can view the live status of every job they have applied for, as updated by the administrator.

---

## 💻 Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP
- **Database:** MySQL (MariaDB via XAMPP)
- **Server:** Apache (via XAMPP)

---

## 🗃️ Database Structure

The project uses a relational database model to ensure data integrity and efficiency.

| Table | Description |
| :--- | :--- |
| `student` | Stores all personal and academic details of students. |
| `staff` | Stores credentials for administrative users. |
| `company` | Contains profiles of all registered companies. |
| `jobs` | Stores details for each job posting, linked to a company. |
| `marks` | A separate table to store students' CGPA and backlogs. |
| `applications` | Tracks every application made by a student for a job. |
| `placements` | The final record of a successful placement, linking a student to a job. |

---

## ⚙️ Installation & Setup Guide

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (with Apache, MySQL, PHP)
- A modern web browser (Chrome, Firefox, Edge)
- A code editor (like VS Code)

### Setup Steps
1.  **Clone or Download:** Download the project repository and extract it.
2.  **Place in `htdocs`:** Copy the project folder (e.g., `CPH`) into your XAMPP installation's `htdocs` directory (usually `C:\xampp\htdocs`).
3.  **Start XAMPP:** Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.
4.  **Import the Database:**
    - Open your web browser and navigate to `http://localhost/phpmyadmin/`.
    - Create a new, empty database named `miniproject`.
    - Select the `miniproject` database and go to the "Import" tab.
    - Choose the `miniproject.sql` file provided in the repository and click "Go".
    - *Alternatively, you can run the large data generation script to populate the portal with 100+ realistic records.*
5.  **Configure Database Connection:**
    - Open the `config.php` file in your project folder.
    - Ensure the `DB_USERNAME` and `DB_PASSWORD` match your MySQL setup. (The project is configured for the default XAMPP user `root` with no password, but creating a dedicated `placement_user` is recommended).
6.  **Access the Project:**
    - Open your browser and navigate to `http://localhost/CPH/` (or your project's folder name). The main landing page should appear.

---

## 🌟 Future Improvements

While the project is fully functional, here are some potential features for future development:

- **Dedicated Company Portal:** Allow company HR to log in, post their own jobs, and view the list of applicants directly.
- **Resume & Document Uploads:** Enable students to upload their resumes, and allow admins/companies to view them.
- **Email Notifications:** Automatically send emails to students when their application status is updated or when a new eligible job is posted.
- **Advanced Analytics:** Create a visual analytics page for Admins with charts and graphs showing placement statistics by department, company, etc.

---

## 👤 Author

- **Armandeep Singh**
