<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");
?>

<div class="container mt-5">
    <h2 class="mb-4">About Us</h2>
    <p>I-rate Movies is a movie review website that allows users to rate and review movies. 
        Every movie has the facility to be able to be reviewed from 1-5 stars and add an additional comment if they choose to.
        The user should be able to edit and delete their reviews either from the movie page or from their account page.
        On the movie detail page the reviewer should be able to see their own review at the top and a list of everyone else's reviews below that in descending chronological order.
        After a user has made 11+ reviews then that user will get a super-reviewer badge.</p>
    </br>
    <hr>
    </br>

    <h3 class="mt-5 mb-3">Frequently Asked Questions</h3>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                How do I create an account?
            </button>
            <div id="faq1" class="accordion-collapse collapse">
                <div class="accordion-body">
                    To create an account, click on the "Sign Up" button at the top right corner of the homepage and fill in the required information.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                How do I submit a review?
            </button>
            <div id="faq2" class="accordion-collapse collapse">
                <div class="accordion-body">
                    To submit a review, navigate to the movie's detail page and click on the "Write a Review" button. You can then rate the movie and add your comments.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                Can I edit or delete my reviews?
            </button>
            <div id="faq3" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Yes, you can edit or delete your reviews from either the movie page or your account page. Simply find your review and click on the appropriate option.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                What is a super-reviewer badge?
            </button>
            <div id="faq4" class="accordion-collapse collapse">
                <div class="accordion-body">
                    A super-reviewer badge is awarded to users who have submitted 11 or more reviews. It is a recognition of their active participation in the community.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
