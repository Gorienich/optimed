document.addEventListener("DOMContentLoaded", function () {
    /***** masseur slider ******/
    const masseurImages = document.querySelectorAll('.masseur-image');
    const masseurRoomImages = document.querySelectorAll('.masseur-room-img');
    const description = document.querySelector('#masseurDescription');
    const prevBtn = document.querySelector('.btn-prev');
    const nextBtn = document.querySelector('.btn-next');
    /***** header ******/
    const headerOpener = document.getElementById("headerOpener");
    const header = document.getElementById("header");
    const closeHeaderOpener = document.getElementById("closeHeaderOpener");
    /***** massage discription ******/
    const gridContainer = document.getElementById("gridContainer");
    const gridConteinerMobile = document.getElementById("gridConteinerMobile");
    /***** mobile massage discription ******/
    const sliderPic = document.querySelectorAll('.descripBox');
    const prevBtnMob = document.querySelector('.btn-prev-mobile');
    const nextBtnMob = document.querySelector('.btn-next-mobile');
    // Share buttons event listeners
    const whatsappShareButton = document.getElementById('whatsappShare');
    const facebookShareButton = document.getElementById('facebookShare');
    const instagramShareButton = document.getElementById('instagramShare');
    const tiktokShareButton = document.getElementById('tiktokShare');
    /***** scroll up button ******/
    const scrollToTopButton = document.getElementById("scrollToTop");

    // intro btn
    const introBtn = document.getElementById('btn-intro');
    const introBlock = document.querySelector('.introduce');
    introBtn.addEventListener('click', () => {
        introBlock.style.display = 'none'; // Corrected this line
    });
    
    /* header config  */
    function toggleHeader() {
        if (window.innerWidth <= 768) {
            header.style.display = "none";
            gridContainer.style.display = "none";
            gridConteinerMobile.style.display = "flex";
            headerOpener.style.display = "block";
            closeHeaderOpener.style.display = "none";
            massageSliderMobile(currentIndex);
        } else {
            header.style.display = "flex";
            gridConteinerMobile.style.display = "none";
            gridContainer.style.display = "flex";
            headerOpener.style.display = "none";
            closeHeaderOpener.style.display = "none";
            expandItem();
        }
    };
    headerOpener.addEventListener('click', () => {
        headerOpener.style.display = "none";
        header.style.display = "flex";
        closeHeaderOpener.style.display = "block";
    });
    closeHeaderOpener.addEventListener('click', () => {
        headerOpener.style.display = "block";
        header.style.display = "none";
        closeHeaderOpener.style.display = "none";
    });
    window.addEventListener('resize', toggleHeader);
    toggleHeader();
    /* end of header config  */

    /*** slider of masseurs container ***/
    const masseurDescription = [
        'Elena professional masseur in aromatherapy and stone massage with experience of more than 10 years',
        'Milisa professional masseur in manual therapy and acupuncture with experience of more than 20 years'
    ];
    var currentIndex = 0;
    function showImage(index) {
        masseurImages.forEach((image, i) => {
            if (i === index) {
                image.style.opacity = 1;
            } else {
                image.style.opacity = 0;
            }
        });
        masseurRoomImages.forEach((image, i) => {
            if (i === index) {
                image.style.opacity = 1;
            } else {
                image.style.opacity = 0;
            }
        });
        // Display the description
        description.textContent = masseurDescription[index];
    }
    function prevImage() {
        currentIndex = (currentIndex - 1 + masseurImages.length) % masseurImages.length;
        showImage(currentIndex);
    };
    function nextImage() {
        currentIndex = (currentIndex + 1) % masseurImages.length;
        showImage(currentIndex);
    };
    prevBtn.addEventListener('click', prevImage);
    nextBtn.addEventListener('click', nextImage);
    showImage(currentIndex); // Show the initial image
    /*****   end of masseur slider  *****/
    /*****   massage discription slider mobile  *****/
    function massageSliderMobile(index) {
        sliderPic.forEach((image, i) => {
            if (i === index) {
                image.style.opacity = 1;
            } else {
                image.style.opacity = 0;
            }
        });
    };
    function prevImageMob() {
        currentIndex = (currentIndex - 1 + sliderPic.length) % sliderPic.length;
        massageSliderMobile(currentIndex);
    };
    function nextImageMob() {
        currentIndex = (currentIndex + 1) % sliderPic.length;
        massageSliderMobile(currentIndex);
    };
    prevBtnMob.addEventListener('click', prevImageMob);
    nextBtnMob.addEventListener('click', nextImageMob);
    /*****   end of massage discription mobile  *****/
    /*****  button to up scroll   *****/
    // Show the scroll button when the user scrolls down
    window.addEventListener("scroll", () => {
        if (window.pageYOffset > header.offsetHeight) {
            scrollToTopButton.classList.add("show");
        } else {
            scrollToTopButton.classList.remove("show");
        }
    });
    // Scroll to the header when the button is clicked
    scrollToTopButton.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth" // Add smooth scrolling
        });
    });
    /*****   end button up scroll   *****/
    /*****   container of massage descriptions  *****/
    function expandItem() {
        const items = document.querySelectorAll('.item');
        items.forEach((item) => {
            item.addEventListener('click', () => {
                item.classList.toggle('expanded');
            });
        });
    };
    /*****  end of container massage descriptions  *****/
    whatsappShareButton.addEventListener('click', shareOnWhatsApp);
    facebookShareButton.addEventListener('click', shareOnFacebook);
    instagramShareButton.addEventListener('click', shareOnInstagram);
    tiktokShareButton.addEventListener('click', shareOnTiktok);
    // Share functions
    function shareOnWhatsApp() {
        const phoneNumber = '0533756636';
        const message = 'Hello, check this out: http://optimed.co.il';
        const whatsappURL = `https://api.whatsapp.com/send?phone=${phoneNumber}&text=${encodeURIComponent(message)}`;
        window.open(whatsappURL, '_blank');
    };
    function shareOnFacebook() {
        const url = 'http://optimed.co.il';
        const facebookURL = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(facebookURL, '_blank');
    };
    function shareOnInstagram() {
        const url = 'http://optimed.co.il';
        const instagramURL = `https://www.instagram.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(instagramURL, '_blank');

    };
    function shareOnTiktok() {
        const url = 'http://optimed.co.il';
        const instagramURL = `https://www.tiktok.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(instagramURL, '_blank');

    };
});
