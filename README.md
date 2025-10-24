# LearnHub

## Project Description
LearnHub is a web application designed to facilitate the sharing and management of educational notes. Users can register, log in, upload their notes, browse notes uploaded by others, search and filter notes, and manage their own uploaded and bookmarked notes through a personalized dashboard.

## Features
- User Registration and Login (including Google authentication)
- Secure User Sessions
- Upload Notes (PDF, DOC, DOCX, JPG, JPEG, PNG formats supported, up to 10MB)
- Drag-and-Drop File Upload Interface
- Browse All Notes with Search, Filter by Subject, and Sort Options
- User Dashboard to view Uploaded Notes and Bookmarked Notes
- Like and Bookmark Notes functionality
- Admin Blog/Profile Page
- Responsive Design for various devices (desktops, laptops, tablets, mobile phones)

## Installation
To set up LearnHub on your local machine, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd LearnHub
    ```
2.  **Set up your web server:**
    Place the `LearnHub` directory in your web server's document root (e.g., `C:\xampp\htdocs\` for XAMPP).
3.  **Database Setup:**
    *   Create a MySQL database named `learnhub`.
    *   Import the `db_setup.sql` file to create the necessary tables:
        ```bash
        mysql -u your_username -p learnhub < db_setup.sql
        ```
    *   Run `add_bookmarks_table.sql` and `add_college_to_notes.sql` for additional features.
4.  **Configure Database Connection:**
    Edit `config/database.php` with your database credentials.
5.  **Configure Google API (for Google Authentication and Drive Upload):**
    *   Follow the instructions in `config/api_keys.php` to set up your Google API credentials.
    *   Ensure `google_api_helper.php` is correctly configured for Google Drive integration.
6.  **Install Composer Dependencies:**
    Navigate to the project root and run:
    ```bash
    composer install
    ```

## Usage
1.  Open your web browser and navigate to `http://localhost/LearnHub` (or your configured URL).
2.  Register a new account or log in with existing credentials.
3.  Explore the dashboard, upload notes, browse other notes, and utilize the search and filter functionalities.

## Technologies Used
-   PHP
-   MySQL (PDO for database interaction)
-   HTML5
-   CSS (Tailwind CSS for styling)
-   JavaScript
-   Google API Client Library for PHP (for Google Authentication and Google Drive integration)

## Project Structure
```
LearnHub/
├── .gitignore
├── add_bookmarks_table.sql
├── add_college_to_notes.sql
├── admin_blog.php
├── api/
│   └── chatbot_service.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   ├── Gemini_Generated_Image_l3jff3l3jff3l3jf.png
│   │   └── WhatsApp Image.jpg.jpg
│   └── js/
│       ├── chatbot.js
│       └── main.js
├── bookmark_note.php
├── composer.json
├── composer.lock
├── config/
│   ├── api_keys.php
│   ├── database.php
│   └── google_api.php
├── dashboard.php
├── db_setup.sql
├── delete_note.php
├── download.php
├── gauth.php
├── google_register.php
├── includes/
│   ├── chatbot.php
│   ├── footer.php
│   ├── google_api_helper.php
│   └── header.php
├── index.php
├── like_note.php
├── login.php
├── logout.php
├── most_liked_notes.php
├── notes.php
├── privacy_policy.php
├── README.md
├── register.php
├── upload.php
├── uploads/
│   ├── 68dd44925b52a_HCI_ass_1_1___1___2_.pdf
│   └── 68de80b09ba4f_MCA_FSD_Model_Answers.docx
└── vendor/
```

*   **`api/`**: Contains API endpoints.
    *   `chatbot_service.php`: Handles the chatbot functionality.
*   **`assets/`**: Holds all static assets like CSS, JavaScript, and images.
*   **`config/`**: Stores configuration files.
    *   `api_keys.php`: For storing API keys (e.g., Google).
    *   `database.php`: For database connection settings.
    *   `google_api.php`: For Google API client configuration.
*   **`includes/`**: Contains reusable PHP components like the header, footer, and helper functions.
*   **`uploads/`**: Default directory for storing uploaded note files.
*   **`vendor/`**: Manages third-party packages installed via Composer.
*   **`.sql` files**: SQL scripts for setting up and updating the database schema.
*   **`.php` files (root)**: Core application files for handling user authentication, note management, and page rendering.

## Database Schema

The database consists of the following tables:

### `users`

Stores user information.

| Column       | Type         | Description                                  |
|--------------|--------------|----------------------------------------------|
| `id`         | INT          | Primary Key, Auto Increment                  |
| `name`       | VARCHAR(100) | User's name                                  |
| `email`      | VARCHAR(100) | User's email, must be unique                 |
| `password`   | VARCHAR(255) | Hashed password                              |
| `college`    | VARCHAR(100) | User's college                               |
| `created_at` | TIMESTAMP    | Timestamp of user registration               |

### `notes`

Stores information about the uploaded notes.

| Column           | Type         | Description                                       |
|------------------|--------------|---------------------------------------------------|
| `id`             | INT          | Primary Key, Auto Increment                       |
| `title`          | VARCHAR(255) | Title of the note                                 |
| `subject`        | VARCHAR(100) | Subject of the note                               |
| `description`    | TEXT         | Description of the note                           |
| `file_name`      | VARCHAR(255) | Original name of the uploaded file                |
| `file_path`      | VARCHAR(255) | Path to the file (e.g., Google Drive file ID)     |
| `file_size`      | INT          | Size of the file in bytes                         |
| `file_type`      | VARCHAR(100) | MIME type of the file                             |
| `uploader_id`    | INT          | Foreign key referencing `users.id`                |
| `uploader_name`  | VARCHAR(100) | Name of the uploader                              |
| `download_count` | INT          | Number of times the note has been downloaded      |
| `like_count`     | INT          | Number of likes the note has received             |
| `college`        | VARCHAR(255) | College associated with the note                  |
| `created_at`     | TIMESTAMP    | Timestamp of when the note was uploaded           |

### `note_likes`

Tracks which users have liked which notes.

| Column       | Type      | Description                               |
|--------------|-----------|-------------------------------------------|
| `id`         | INT       | Primary Key, Auto Increment               |
| `user_id`    | INT       | Foreign key referencing `users.id`        |
| `note_id`    | INT       | Foreign key referencing `notes.id`        |
| `created_at` | TIMESTAMP | Timestamp of when the like was given      |

### `note_bookmarks`

Tracks which users have bookmarked which notes.

| Column       | Type      | Description                               |
|--------------|-----------|-------------------------------------------|
| `id`         | INT       | Primary Key, Auto Increment               |
| `user_id`    | INT       | Foreign key referencing `users.id`        |
| `note_id`    | INT       | Foreign key referencing `notes.id`        |
| `created_at` | DATETIME  | Timestamp of when the bookmark was created|

## API Documentation

### Chatbot API

This project includes a chatbot service powered by the Gemini API.

- **Endpoint:** `api/chatbot_service.php`
- **Method:** `POST`
- **Request Body:**
  ```json
  {
    "question": "Your question for the chatbot."
  }
  ```
- **Success Response:**
  ```json
  {
    "answer": "The chatbot's response."
  }
  ```
- **Error Response:**
  ```json
  {
    "error": "A description of the error."
  }
  ```

**Functionality:**

The endpoint takes a user's question, sends it to the Gemini API, and returns the generated response. It uses the `gemini-pro-latest` model by default, with a fallback to `gemini-flash-latest` in case of overload.

**Configuration:**

To use the chatbot, you must set your `GEMINI_API_KEY` in the `config/api_keys.php` file.

## Configuration

The main configuration files are located in the `config/` directory.

### `config/database.php`

This file contains the database connection settings. You need to update the following variables to match your local environment:

- `$host`: The hostname of your database server (e.g., "localhost").
- `$db_name`: The name of your database (e.g., "learnhub").
- `$username`: The username for your database.
- `$password`: The password for your database.

### `config/api_keys.php`

This file contains the API keys for the Google services used in the project.

- `GEMINI_API_KEY`: Your API key for the Gemini API, used for the chatbot functionality.
- `GOOGLE_API_KEY`: Your Google API key, used for Google Drive integration.
- `GOOGLE_CLIENT_ID`: Your Google OAuth 2.0 Client ID.
- `GOOGLE_CLIENT_SECRET`: Your Google OAuth 2.0 Client Secret.
- `GOOGLE_REDIRECT_URI`: The redirect URI for Google OAuth 2.0, which should point to `gauth.php` on your server.
- `GOOGLE_DRIVE_FOLDER_ID`: The ID of the Google Drive folder where uploaded notes will be stored.

## Pages Overview

-   **`index.php`**: The landing page of the website, providing an introduction to LearnHub.
-   <img width="1779" height="882" alt="Screenshot 2025-10-06 183929" src="https://github.com/user-attachments/assets/45c80c9b-bec6-40a4-b110-1be82fc5edd4" />

    <img width="1762" height="876" alt="Screenshot 2025-10-06 184016" src="https://github.com/user-attachments/assets/f2303683-b340-45b6-a963-ee988c4e143e" />
    
    <img width="1771" height="896" alt="Screenshot 2025-10-06 184035" src="https://github.com/user-attachments/assets/77936f37-b96e-4634-9cd8-0cbe532f5a01" />
    
    <img width="1768" height="893" alt="Screenshot 2025-10-06 184055" src="https://github.com/user-attachments/assets/fb9bc60a-f820-4d5e-a2ea-2b98cba2ce0a" />
    
-   **`register.php`**: Allows new users to create an account.
-   <img width="913" height="913" alt="Screenshot 2025-10-06 184130" src="https://github.com/user-attachments/assets/4b2ebfd3-8084-4459-878e-4f1fa172300a" />

-   **`login.php`**: Allows existing users to log in.
-   <img width="1628" height="902" alt="Screenshot 2025-10-06 184157" src="https://github.com/user-attachments/assets/afee5806-e307-4459-a9d6-563b76febb5c" />

-   **`gauth.php` & `google_register.php`**: Handle the Google authentication and registration process.
-   <img width="1624" height="717" alt="Screenshot 2025-10-06 193315" src="https://github.com/user-attachments/assets/664e0612-d34a-4356-a2ae-eb4679f41086" />

-   **`dashboard.php`**: The user's personal dashboard, showing their uploaded notes, bookmarked notes, and statistics.
-   <img width="1865" height="888" alt="Screenshot 2025-10-06 184328" src="https://github.com/user-attachments/assets/1b7423e0-88b3-4c5e-96a4-997f2da0fa7b" />

-   **`upload.php`**: A page with a form to upload new notes.
-   <img width="981" height="893" alt="Screenshot 2025-10-06 184304" src="https://github.com/user-attachments/assets/5787d763-7d44-4e6b-bb38-43c8be5ba241" />

-   **`notes.php`**: The main page for browsing, searching, and filtering all available notes.
-   <img width="1557" height="890" alt="Screenshot 2025-10-06 184238" src="https://github.com/user-attachments/assets/01ed38eb-6b29-4069-9d5b-4bdbabf5b538" />

-   **`download.php`**: A script that handles the downloading of note files.
-   **`delete_note.php`**: A script that handles the deletion of a user's own notes.
-   **`like_note.php`**: A script that processes the liking and unliking of notes.
-   **`bookmark_note.php`**: A script that handles the bookmarking and unbookmarking of notes.
-   **`admin_blog.php`**: A page for administrators to post and manage blog content.
-   <img width="1621" height="893" alt="Screenshot 2025-10-06 184358" src="https://github.com/user-attachments/assets/e6d0a6d8-67fc-4d19-9e37-58a962ca7297" />

-   **`privacy_policy.php`**: A page displaying the website's privacy policy.
-   <img width="682" height="732" alt="Screenshot 2025-10-06 184435" src="https://github.com/user-attachments/assets/c473b8af-f468-4996-950c-0247b849a9af" />

-   **`logout.php`**: A script to log the user out and end their session.

-   ##CHATBOT
-   An AI-powered virtual assistants designed to interact with users in natural language, simulating human-like conversations.it can clarify the users douth's.
-   <img width="1773" height="885" alt="Screenshot 2025-10-06 194159" src="https://github.com/user-attachments/assets/e3e60da3-24ca-43e8-a747-0f46e6c6d07e" />
    <img width="1804" height="902" alt="Screenshot 2025-10-06 194237" src="https://github.com/user-attachments/assets/10cc2baa-cc1a-4b2c-84f2-00029fcf4bea" />


  Why PHP?

  PHP is a server-side scripting language, meaning it runs on the server to generate dynamic content before
  it's sent to a user's browser.

   * Designed for the Web: PHP was built specifically for web development, making it very effective at
     handling tasks like processing forms, managing user sessions, and interacting with databases.
   * Easy to Learn and Use: It has a relatively gentle learning curve, which allows for faster development.
     Its syntax is straightforward and easy to embed directly into HTML.
   * Large Community and Ecosystem: PHP has been around for a long time and has a massive community. This
     means you can find a vast number of tutorials, libraries (like the ones managed by Composer in this
     project), and frameworks to help you build and extend your application.
   * Cost-Effective: As an open-source language, PHP is free to use. It is supported by virtually all web
     hosting providers, often on their most affordable plans, which keeps deployment costs low.

  Why MySQL?

  MySQL is a relational database management system (RDBMS), which is used to store and manage the
  application's data in a structured way.

   * Reliability and Performance: MySQL is a mature, stable, and high-performance database system. It's
     trusted by many of the world's largest companies (like Facebook and YouTube) to manage their data.
   * Structured Data: For an application like LearnHub, the data is naturally structured—you have users,
     notes, likes, and bookmarks that all relate to each other. A relational database like MySQL is perfect
     for organizing and querying this kind of data efficiently.
   * Ease of Use: MySQL is known for being easy to set up and manage. Its query language (SQL) is the industry
      standard and is very well-documented.
   * Open Source: Like PHP, MySQL is open-source, making it a free and cost-effective solution for storing
     your project's data.

  Why Use Them Together?

  PHP and MySQL are the core of the popular "LAMP" stack (Linux, Apache, MySQL, PHP), a combination that has
   powered a significant portion of the web for decades.

   * Seamless Integration: PHP has excellent built-in support for MySQL, allowing for a very smooth and
     efficient connection between your application code and your database. This project uses PDO (PHP Data
     Objects) for this purpose, which is a modern and secure way to interact with a database in PHP.
   * Proven and Reliable: The combination is well-tested, proven, and known to be a reliable choice for
     building dynamic, database-driven websites of all sizes.
   * Rapid Development: Because they are both easy to use and work so well together, PHP and MySQL allow
     developers to build and launch applications like LearnHub relatively quickly.

  In short, PHP and MySQL provide a practical, cost-effective, and powerful foundation for building a
  dynamic web application like LearnHub.



## Advantages of LearnHub

-   **Centralized Note Sharing:** Provides a single, organized platform for students to discover and share study materials.
-   **Easy Access to Resources:** Users can quickly search, filter, and download notes for a wide range of subjects and colleges.
-   **Collaborative Learning:** The like and download counts help students identify the most popular and highest-quality notes.
-   **Personalized Experience:** The user dashboard allows individuals to manage their uploaded content, track its popularity,    and curate a personal collection of bookmarked notes.
-   **Secure File Storage:** Integrates with Google Drive for reliable and secure file storage.
-   **Modern User Interface:** Features a clean, responsive, and user-friendly design that works across all devices.
-   **Open Source:** The project is open-source, which means it is transparent and can be extended by the community.

## Contributing
Contributions are welcome! Please feel free to fork the repository, make your changes, and submit a pull request.

## License
This project is open-source and available under the [MIT License](LICENSE). (Note: A `LICENSE` file is not currently present in the provided file list, but this is a common open-source license.)

## Contact
For any inquiries, please contact Durga Vara Prasad Pappuri at pappuridurgavaraprasad4pl@gmail.com or visit [VARA4u-tech on GitHub](https://github.com/VARA4u-tech).
