i-rate movies setup info:

Setup (For Vivian)

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

Still to do / finish doing:

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
20. Prepare the presentation for the class.

Trello :
https://trello.com/invite/b/69b75009c187e6b32845351d/ATTI03d99de0f7e8b3aa66221a5d8d8e1b48AF620EAC/i-rate-movie
