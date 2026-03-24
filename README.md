**i-rate Movies**

Is a simple web app for rating and reviewing movies developed by Michael and Wendy using mostly PHP/MySQL as well as a little bit of HTML & Java.
Front end users can register, log in/out, browse movies, leave star reviews from 1–5 and add comments. Users can also manage their own reviews and their accounts and delete their accounts if they wish.

Features

- Users can register with an alias and the php will track their session when navigating.
- Movie listings should appear on the home page in random sequence whenever the page is refreshed and there is also a separate page for the details of the movie if you happen to click on the movie.
- Every movie has the facility to be able to be reviewed from 1-5 stars and add an additional comment if they choose to.
- The user should be able to edit and delete their reviews either from the movie page or from their account page.
- On the movie detail page the reviewer should be able to see their own review at the top and a list of everyone else's reviews below that in descending chronological order.
- After a user has made 11+ reviews then that user will get a super-reviewer badge.

Tech Stack

- PHP
- MySQL
- Bootstrap for layout/styling
- Pencil / stars / bin icons from bootstrap
- Xampp for database tables and for local database hosting / developing.

Setup (For Wendy / Vivian)

1. Clone the repository:

   ```bash
   git clone https://github.com/micstusmi/i-rate_movies.git
2. Place the project in your web root, e.g. on XAMPP:

C:\xampp\htdocs\i-rate_movies\
Create a MySQL database (e.g. i_rate_movies) and import the SQL schema:

-- Example
CREATE DATABASE i_rate_movies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE i_rate_movies;

-- Then import schema from /path/to/your/schema.sql (if you have one in the repo)
Configure database connection in includes/db.php:

$conn = new mysqli('localhost', 'root', '', 'i_rate_movies');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
Start Apache and MySQL (e.g. via XAMPP Control Panel), then open:

http://localhost/i-rate_movies/ – home page
http://localhost/i-rate_movies/login.php – login
http://localhost/i-rate_movies/register.php – registration
http://localhost/i-rate_movies/my_account.php – account and reviews
Usage notes
You must register and log in to:
Leave a review on a movie detail page
Edit or delete your own reviews
On a movie page:
If you haven’t reviewed the movie yet, you’ll see a star rating form.
If you already reviewed it, your review appears at the top with edit/delete buttons.
Other users’ reviews are listed below and are read-only.
On “My Account”:
All your reviews are listed with quick edit (pencil) and delete controls.
You can adjust the rating by clicking on the stars in the edit form.
Known limitations / possible improvements
No password reset flow yet.
No pagination on movie lists or reviews.
Input validation and error handling are minimal.

If you want to mention your collaborator specifically, add a short section:

```md
## Contributors

- Your Name (your role, e.g. backend, database, etc.)
- Collaborator Name (their role, e.g. UI, testing, etc.)
That’s enough for a teacher:

It explains what the app does.
It tells them how to run it.
It shows that you understand the structure and features.
