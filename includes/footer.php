</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Generic function to wire up a star rating widget
  function setupStarWidget(container, hiddenInput, textEl, initialRating) {
    if (!container || !hiddenInput) return;

    const stars = container.querySelectorAll('.star');

    function setStars(rating) {
      stars.forEach(star => {
        const value = parseInt(star.getAttribute('data-value'), 10);
        if (value <= rating) {
          star.classList.remove('bi-star');
          star.classList.add('bi-star-fill', 'text-warning');
        } else {
          star.classList.remove('bi-star-fill', 'text-warning');
          star.classList.add('bi-star');
        }
      });

      hiddenInput.value = rating || '';
      if (textEl) {
        textEl.textContent = rating
          ? `You selected ${rating} star${rating > 1 ? 's' : ''}.`
          : '';
      }
    }

    stars.forEach(star => {
      star.style.cursor = 'pointer';

      star.addEventListener('click', function () {
        const value = parseInt(this.getAttribute('data-value'), 10);
        setStars(value);
      });

      star.addEventListener('mouseover', function () {
        const value = parseInt(this.getAttribute('data-value'), 10);
        setStars(value);
      });
    });

    container.addEventListener('mouseleave', function () {
      const selected = parseInt(hiddenInput.value || '0', 10);
      setStars(selected);
    });

    // Initialize with existing rating if provided
    if (initialRating) {
      setStars(initialRating);
    }
  }

  // === 1) Movie page: new-review widget (id="star-rating") ===
  (function () {
    const container = document.getElementById('star-rating');
    const input     = document.getElementById('rating-value');
    const textEl    = document.getElementById('rating-text');
    if (container && input) {
      setupStarWidget(container, input, textEl, 0);
    }
  })();

  // === 2) Movie page: edit-review widget (id="edit-star-rating") ===
  (function () {
    const container = document.getElementById('edit-star-rating');
    const input     = document.getElementById('edit-rating-value');
    const textEl    = document.getElementById('edit-rating-text');
    if (container && input) {
      const initialRating = parseInt(container.getAttribute('data-initial-rating') || '0', 10);
      setupStarWidget(container, input, textEl, initialRating);
    }
  })();

  // === 3) My account page: many edit widgets, ids like "star-rating-<id>" ===
  (function () {
    const containers = document.querySelectorAll('[id^="star-rating-"]');
    containers.forEach(container => {
      const idSuffix = container.id.replace('star-rating-', '');
      const input    = document.getElementById('rating-value-' + idSuffix);
      const textEl   = document.getElementById('rating-text-' + idSuffix);
      const initialRating = parseInt(
        container.getAttribute('data-initial-rating') || input.value || '0',
        10
      );
      setupStarWidget(container, input, textEl, initialRating);
    });
  })();

  // === 4) Toggle edit form for "my review" on movie page ===
  (function () {
    const editBtn        = document.getElementById('edit-my-review-btn');
    const editForm       = document.getElementById('edit-my-review-form');
    const commentDisplay = document.getElementById('my-review-comment-display');
    const cancelEditBtn  = document.getElementById('cancel-edit-my-review');

    if (editBtn && editForm) {
      editBtn.addEventListener('click', function () {
        editForm.classList.remove('d-none');
        if (commentDisplay) commentDisplay.classList.add('d-none');
      });
    }

    if (cancelEditBtn && editForm) {
      cancelEditBtn.addEventListener('click', function () {
        editForm.classList.add('d-none');
        if (commentDisplay) commentDisplay.classList.remove('d-none');
      });
    }
  })();
});
</script>

</body>
</html>