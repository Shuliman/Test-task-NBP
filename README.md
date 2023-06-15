# Currency Converter

This is a test project for a simple currency converter that uses data from NBP (Narodowy Bank Polski) to convert an amount from one currency to another.

## Installation and Setup Instructions

1. Clone the repository to your local machine:

2. Rename the `config.example.php` file to `config.php` and update the necessary configuration values, such as database credentials.

3. Create a MySQL database and execute the following SQL query to create the necessary table for storing conversion results:

```sql
CREATE TABLE IF NOT EXISTS conversion_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amount DECIMAL(10, 4) NOT NULL,
  source_currency VARCHAR(3) NOT NULL,
  target_currency VARCHAR(3) NOT NULL,
  converted_amount DECIMAL(10, 4) NOT NULL,
  date DATETIME NOT NULL
); 
```

4. Ensure that you have PHP and the necessary extensions installed on your server or local environment.

5. Start the PHP development server or configure a virtual host to serve the project.