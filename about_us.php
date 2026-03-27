<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");
?>
<div class="container">
    <h2>About Us</h2>
    <p>I-rate Movies is a movie review website that allows users to rate and review movies. 
        Every movie has the facility to be able to be reviewed from 1-5 stars and add an additional comment if they choose to.
        The user should be able to edit and delete their reviews either from the movie page or from their account page.
        On the movie detail page the reviewer should be able to see their own review at the top and a list of everyone else's reviews below that in descending chronological order.
        After a user has made 11+ reviews then that user will get a super-reviewer badge.</p>

    <h3>Frequently Asked Questions</h3>
    <div class="faq">
        <button class="faq-btn">How do I create an account?</button>
        <p>To create an account, click on the "Sign Up" button at the top right corner of the homepage and fill in the required information.</p>

        <button class="faq-btn">How do I submit a review?</button>
        <p>To submit a review, navigate to the movie's detail page and click on the "Write a Review" button. You can then rate the movie and add your comments.</p>
        </div>
        <button class="faq-btn">Can I edit or delete my reviews?</button>
        <p>Yes, you can edit or delete your reviews from either the movie page or your account page. Simply find your review and click on the appropriate option.</p>
        </div>

        <button class="faq-btn">What is a super-reviewer badge?</button>
        <p>A super-reviewer badge is awarded to users who have submitted 11 or more reviews. It is a recognition of their active participation in the community.</p>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
