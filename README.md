# JARA — An Advanced Todo List

This is the backend for JARA, built with Laravel.

## Setup Instructions

1. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration:**
   Copy the `.env.example` file and configure your local environment (the `.env` file is ignored by Git).
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup:**
   Run the migrations to set up the database.
   ```bash
   php artisan migrate
   ```

4. **Running the Application:**
   Start the Laravel development server:
   ```bash
   php artisan serve
   ```
   Start the Vite development server (if using frontend assets):
   ```bash
   npm run dev
   ```
