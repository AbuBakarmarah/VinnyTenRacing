
let slideIndex = 1;

document.addEventListener('DOMContentLoaded', () => {
  initSlideshow();
});

function initSlideshow() {
  const slides = document.getElementsByClassName('slide');
  if (!slides.length) return;   // no slideshow on this page

  showSlides(slideIndex);

  // Auto slideshow
  setInterval(() => {
    plusSlides(1);
  }, 5000);
}

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail / dot controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  const slides = document.getElementsByClassName('slide');
  const dots   = document.getElementsByClassName('dot');

  if (!slides.length) return;

  if (n > slides.length) { slideIndex = 1; }
  if (n < 1)             { slideIndex = slides.length; }

  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = 'none';
  }
  for (let i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(' active', '');
  }

  slides[slideIndex - 1].style.display = 'block';
  if (dots[slideIndex - 1]) {
    dots[slideIndex - 1].className += ' active';
  }
}

/* Expose functions for inline onclick handlers */
window.plusSlides = plusSlides;
window.currentSlide = currentSlide;
