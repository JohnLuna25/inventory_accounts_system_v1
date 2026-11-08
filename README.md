Inventory and Accounts Management System for Sorcom Ready-Mix Concrete
A web-based management system designed to streamline the daily operations of a ready-mix concrete business. This project features two interconnected modules, Inventory and Accounts, working together to manage raw ingredients, ready-mix concrete stocks, and customer orders in real-time.

Tech Stack
- Frontend: HTML, CSS, Bootstrap, JavaScript
- Backend: PHP
- Database: MySQL (managed via phpMyAdmin)
- Local Server: XAMPP


Key Features

Inventory System
Designed for the Plant Manager to maintain full control over stock levels.
• Dual Inventory Tracking: Monitors both raw ingredients (sand, cement, gravel, etc.) and wet ready-mix concrete.
•Automated Deductions: Automatically calculates and deducts raw ingredients when a new batch of ready-mix concrete is produced.
•Role-Based Access: Full access restricted to the Plant Manager.

Accounts System
Designed for Accountants to handle customer transactions and order processing.
• Customer Management: Input and track customer information, delivery dates, concrete type, and order quantities.
• Smart Order Processing: Before finalizing an order, the system checks ingredient availability.
• Logic Flow: If there is no stock for the specific concrete type, the system deducts from the raw ingredients. If there is already existing wet concrete stock, it bypasses the ingredients and deducts directly from the ready-mix inventory.
• Shared Access: Accessible by both Accountants and the Plant Manager.

General Features
• Real-Time Updates: UI updates dynamically without needing to refresh the page.
• Live Clock: Real-time date and time display on the dashboard.
• Data Visualization: Generates visual representations of stock levels, sales, and frequent customer purchases.
• Account Management: Users can securely update their name, email, and password.
• Responsive UI: Fully optimized for both desktop and mobile devices using Bootstrap.

System Workflow
1. Input: Accountant inputs customer details, delivery date, and concrete order.
2. Validation: System checks if the requested concrete type is already in the wet concrete inventory.
3. Execution (Path A - Ready-Mix Available): If stock exists, the system deducts directly from the ready-mix inventory.
4. Execution (Path B - No Ready-Mix): If stock is zero, the system checks raw ingredient availability. Once verified, it deducts the raw materials and logs the production of the new concrete.
5. Output: Generates an updated record and updates the dashboard reports.

Installation and Setup
To run this project locally, follow these steps:

1. Clone the repository:
git clone https://github.com/JohnLuna25/inventory_accounts_system_v1.git

2. Move to server directory: Place the cloned folder inside your XAMPP htdocs directory.

3. Set up the Database:
• Open phpMyAdmin.
• Create a new database (e.g., sorcom_inventory_db).
• Import the provided .sql file (found in the /database folder).

4. Configure Database Connection:
• Locate the database configuration file (e.g., config.php or db_connect.php).
• Update the username, password, and database name to match your local setup.

5. Run the Application:
• Start Apache and MySQL in XAMPP.
• Open your browser and go to http://localhost/inventory_accounts_system_v1

Developer
John Noli D. Villaluna
GitHub: https://github.com/JohnLuna25
