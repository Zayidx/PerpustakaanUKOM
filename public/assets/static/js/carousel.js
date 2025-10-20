(function () {
    const carousels = document.querySelectorAll('.featured-carousel');

    carousels.forEach((carousel) => {
        const track = carousel.querySelector('.featured-carousel-track');
        const prevButton = carousel.querySelector('[data-carousel="prev"]');
        const nextButton = carousel.querySelector('[data-carousel="next"]');
        const slides = Array.from(track.querySelectorAll('.book-slide'));
        let slideWidth = slides[0]?.offsetWidth || 0;
        let index = 0;

        const updateControls = () => {
            const maxIndex = slides.length - Math.round(track.offsetWidth / slideWidth);
            prevButton.style.display = index <= 0 ? 'none' : 'flex';
            nextButton.style.display = index >= maxIndex ? 'none' : 'flex';
        };

        const moveToSlide = (newIndex) => {
            slideWidth = slides[0]?.offsetWidth || slideWidth;
            index = Math.max(0, Math.min(newIndex, slides.length - 1));
            track.style.transform = `translateX(-${slideWidth * index}px)`;
            updateControls();
        };

        prevButton?.addEventListener('click', () => moveToSlide(index - 1));
        nextButton?.addEventListener('click', () => moveToSlide(index + 1));

        // touch support
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        });

        track.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
        });

        track.addEventListener('touchend', () => {
            if (!isDragging) return;
            const diff = startX - currentX;
            if (diff > 50) {
                moveToSlide(index + 1);
            } else if (diff < -50) {
                moveToSlide(index - 1);
            }
            isDragging = false;
        });

        window.addEventListener('resize', () => {
            moveToSlide(index);
        });

        updateControls();
    });
})();
