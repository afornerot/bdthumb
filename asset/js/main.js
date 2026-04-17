var carouselState = {
    currentSlide: 0,
    interval: null
};

window.moveSlide = function(direction) {
    var carousel = document.getElementById('carousel');
    if (!carousel) return;
    
    var track = document.getElementById('carousel-track');
    var slides = track.querySelectorAll('.carousel-slide');
    var totalSlides = slides.length;
    
    carouselState.currentSlide = (carouselState.currentSlide + direction + totalSlides) % totalSlides;
    updateCarousel();
};

window.goToSlide = function(index) {
    carouselState.currentSlide = index;
    updateCarousel();
};

function updateCarousel() {
    var track = document.getElementById('carousel-track');
    var dots = document.querySelectorAll('.carousel-dot');
    var slides = track.querySelectorAll('.carousel-slide');
    
    track.style.transform = 'translateX(-' + (carouselState.currentSlide * 100) + '%)';
    dots.forEach(function(dot, i) {
        dot.classList.toggle('active', i === carouselState.currentSlide);
    });
}

function startCarousel() {
    var carousel = document.getElementById('carousel');
    if (!carousel) return;
    
    var track = document.getElementById('carousel-track');
    var slides = track.querySelectorAll('.carousel-slide');
    if (slides.length <= 1) return;
    
    carouselState.interval = setInterval(function() {
        var slides = track.querySelectorAll('.carousel-slide');
        carouselState.currentSlide = (carouselState.currentSlide + 1) % slides.length;
        updateCarousel();
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    Fancybox.bind("[data-fancybox]", {});

    var lo = document.getElementById("loading-overlay");
    if (lo) {
        lo.style.opacity = "0";
        setTimeout(function() {
            lo.style.display = "none";
        }, 500);
    }

    startCarousel();
});