# 🏙️ Smart City Operations & Resource Tracking System (SCORTS)

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

SCORTS is a comprehensive digital governance platform designed to streamline city management by connecting citizens directly with administrative departments. It provides a transparent, efficient, and real-time environment for reporting and resolving public grievances.

🚀 Key Features

### 👥 User Roles & Access Control
- **Super Admin**: System-wide oversight, department creation, and user management.
- **Department Admin**: Manage department-specific complaints and assign tasks to field workers.
- **Field Worker**: Receive task assignments, update resolution progress, and submit completion reports.
- **Citizen**: Lodge complaints, track resolution status, and receive automated notifications.

🛠️ Core Modules
- **Complaint Management**: Categorized reporting for Water, Electricity, Sanitation, and Roads.
- **Real-time Tracking**: Live status updates from 'Pending' to 'Resolved'.
- **Task Assignment System**: Seamless workflow from department admins to field workers.
- **Automated Notifications**: Real-time in-app alerts, email notifications (via PHPMailer), and SMS simulations for critical updates.
- **Analytics Dashboard**: Visual insights into department efficiency and complaint trends.
- **Audit Trails**: Complete logging of status changes for accountability.

💻 Tech Stack

- **Frontend**: HTML5, Vanilla CSS3 (Custom Design System), JavaScript (ES6+).
- **Backend**: PHP 8.x.
- **Database**: MySQL.
- **Icons**: FontAwesome 6.0+.

🛠️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/index.html) or any WAMP/LEMP stack.
- PHP 7.4 or higher.
- MySQL 5.7 or higher.

Steps
1. **Clone the Project**
   ```bash
   git clone https://github.com/prakash200514/City-Management-System.git
   ```
 **Move to Web Root**
   Move the `city` folder to your `htdocs` (XAMPP) or `/var/www/html` directory.

3. **Database Configuration**
   - Open PHPMyAdmin and create a database named `smart_city`.
   - Import `database/schema.sql`.
   - **OR** simply run `setup_db.php` in your browser: `http://localhost/city/setup_db.php`.

4. **Initialize Users**
   Run `seed_users.php` to generate default administrative and test accounts:
   `http://localhost/city/seed_users.php`.

5. **Start Using**
   Access the application at `http://localhost/city/index.php`.

🔑 Demo Credentials

After running `seed_users.php`, you can use the following credentials to explore the system:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@smartcity.com` | `admin123` |

*Note: For other roles (Dept Admin, Field Worker, Citizen), you can create them via the Super Admin dashboard or use the Registration page.*

📂 Directory Structure

├── assets/             # CSS styles, JS logic, and images
├── config/             # Database connection and configurations
├── database/           # SQL schema and migration scripts
├── includes/           # Reusable UI components (Header, Footer)
├── pages/              # Core application logic and modules
│   ├── auth/           # Login, Registration, and Logout
│   ├── complaints/     # Lodge and View complaint logic
│   ├── dashboard/      # Role-specific dashboard views
│   └── notifications/  # Notification handling
├── index.php           # Landing Page
├── setup_db.php        # Database initialization script
└── seed_users.php       # Initial data seeding script
```
📝 License

Distributed under the MIT License. See `LICENSE` for more information.

📧 Contact

**Prakash** - [prakash200514](https://github.com/prakash200514)

Project Link: [https://github.com/prakash200514/City-Management-System](https://github.com/prakash200514/City-Management-System)
