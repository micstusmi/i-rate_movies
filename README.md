To visit this web application put this URL: https://i-rate-movies.infinityfreeapp.com/?i=1 into your browser.

Legacy Setup (For Vivian) i-rate movies setup info:

1. Download the repository from GitHub.
2. Save everything in C:\xampp\htdocs\i-rate_movies\
3. Create a MySQL database called i-rate_movies and import the SQL schema found in the database folder from the download.
4. Open a browser page and put http://localhost/i-rate_movies/ into the URL and you should see the website working.

-----------------------------------------------------------------

Brief:

i-rate movies is a simple web app for rating and reviewing movies developed by Michael and Wendy using mostly PHP/MySQL as well as a little bit of HTML & Java.
Front end users can register, log in/out, browse movies, leave star reviews from 1–5 and add comments. Users can also manage their own reviews and their accounts and delete their accounts if they wish.

Features

- Users can register with an alias and the php will track their session when navigating.
- Movie listings should appear on the home page in random sequence whenever the page is refreshed and there is also a separate page for the details of the movie if you happen to click on the movie.
- Every movie has the facility to be able to be reviewed from 1-5 stars and add an additional comment if they choose to.
- The user should be able to edit and delete their reviews either from the movie page or from their account page.
- On the movie detail page the reviewer should be able to see their own review at the top and a list of everyone else's reviews below that in descending chronological order.
- After a user has made 11+ reviews then that user will get a super-reviewer badge.

Web Dev Tech Stack

- PHP
- MySQL
- Bootstrap for layout/styling plus pencil / stars / bin icons.
- Xampp for database tables and for local database hosting / developing.
- HTML
- JavaScript
- Ajax

*Below is a list of completed features added for assignment number 3 (except email verification was going to be too time consuming to complete and unneccesary for this assignment):

1. Email validation – no email sending / verification yet.
2. Global search bar.
3. Automatic avg_rating updates – avg_rating has been created but not recalculated on review changes yet.
4. Display average rating & review count on movie.php – not yet.
5. Super Reviewer styling in reviews – badge and coloured/bold aliases inside review lists.
6. Password change facility – no change‑password workflow.
7. Privacy dashboard page which might end up getting nested in the help page.
8. Extra sort/filter options:
9. Lowest rated filter.
10. Rating bands (1–5).
11. Newest ratings (by review date).
12. About / Help page.
13. Genres link / genres page – currently handled by sidebar only.
14. Make the site look like it does in our wireframes with the colours and everything.
15. Fix the filters that aren't working properly.
16. Fix the genres.
17. Figure out the global search so that users can search by actor name.
18. Show user “You have written X amount of reviews”.
19. Show user their progress to becoming a Super Reviewer (e.g. 8/11).
20. Make it so that the user can either login via their email address or their alias.
21. Prepare the presentation for the class.

Trello :
https://trello.com/invite/b/69b75009c187e6b32845351d/ATTI03d99de0f7e8b3aa66221a5d8d8e1b48AF620EAC/i-rate-movie

Task / Item
The Final Website you submit must include the following functional elements:
● Home page displaying random products from your chosen digital media collection
● Navigation menu with dropdowns where necessary
● Category filtering by genre (e.g., Comics, Romance, Thriller, Fiction, Non-fiction, etc.)
● Detailed view page for each product containing:
○ Title, synopsis/description
○ Relevant metadata (e.g., director, author, stars)
○ Form to submit reviews
● User registration and sign-in system
● Product rating system (star rating, Likert scale, or similar)
● Search functionality to locate products quickly
● User favorites list with options to add, remove, and manage favorites

Brief
● User account page where users can:
○ View and manage their submitted reviews
○ Update account details such as email, password, and username
Assessment Instructions
Your submission must include:
● Complete project source code and all assets
● Export of the database in SQL format (.sql file)
● The link to your live website.
● Your code must be hosted on a public GitHub repository
● Provide a link to the repository
● Ensure your website is fully functional and bug-free
● Test all features before submission
● Provide any deployment or setup instructions if applicable in a README file
● For online hosting, ensure the URL is accessible and stable at time of grading

Implementation and demonstration of a wish/favorite list:

Excellent demonstration of the developer’s ability to implement a functioning user generated lists, including a wish or favorite list.

Implementation and demonstration of a search functionality

Excellent demonstration of the developer’s ability to implement a content discovery in the form of search function in a website to assist users in discovering content.

Asynchronous Data

Excellent demonstration of the ability to implement asynchronous data through front end data requests, using Ajax. This include the use of jQuery Ajax function in multiple occasions to update the website content.

Implementation and demonstration of promotion list

Excellent demonstration of the Implementation of other lists like promotions list, new arrival books list, or new movies …etc. The function performs all of its intended functions.

Implementation and demonstration of Products' rating/review and feedback form

Excellent Implementation and demonstration of a review or rating system for products and implementation of a Feedback form that submit reviews for products through the application.
The function performs all of its intended functions.

Implementation of a user account page

An excellent implementation and demonstration of a user account page where users can view any reviews they have previously added and manage them, they also have the option to update their account details, like updating their email, password or the username.
The function performs all of its intended functions.

Source code repository and website hosting

An excellent demonstration of the developer’s ability to use and maintain a source code repository (GitHub) as part of their development process. deployment of the website on a live server or live host such as InfinityFree, GoogieHost or AwardSpace.
