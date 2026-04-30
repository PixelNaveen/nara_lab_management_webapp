/**
 * Forms Carousel JavaScript - LOGIC FIXED
 * Handles context-aware printing of active form slides.
 */

/**
 * Print the currently active carousel slide only.
 */
function printActiveSlide() {
  const activeSlide = document.querySelector('.carousel-item.active');
  const allSlides = document.querySelectorAll('.carousel-item');
  
  if (!activeSlide) {
    alert("⚠️ Could not detect the active form to print.");
    return;
  }

  // Store references to elements to hide
  const controls = document.querySelector(".modal-header-custom");
  const carouselControls = document.querySelectorAll(
    ".carousel-control-prev, .carousel-control-next, .carousel-indicators"
  );
  
  const elementsToHide = [];
  
  if (controls) {
    elementsToHide.push({ el: controls, originalDisplay: controls.style.display });
    controls.style.display = "none";
  }
  carouselControls.forEach((el) => {
    elementsToHide.push({ el: el, originalDisplay: el.style.display });
    el.style.display = "none";
  });

  // Hide all non-active slides
  const hiddenSlides = [];
  allSlides.forEach((slide) => {
    if (slide !== activeSlide) {
      hiddenSlides.push({ el: slide, originalDisplay: slide.style.display });
      slide.style.display = "none";
      slide.classList.remove("active"); // Temporarily strip active to prevent animation glitches
    }
  });

  // Temporarily force active slide to be block for printing
  const activeOriginalDisplay = activeSlide.style.display;
  activeSlide.style.display = "block";

  // Restoration logic
  const restoreAfterPrint = () => {
    // Restore UI elements
    elementsToHide.forEach((item) => {
      item.el.style.display = item.originalDisplay || "";
    });

    // Restore hidden slides
    hiddenSlides.forEach((item) => {
      item.el.style.display = item.originalDisplay || "";
    });
    
    // Restore active slide
    activeSlide.style.display = activeOriginalDisplay || "";
    
    // Re-add active class to things that should have it
    activeSlide.classList.add("active");

    window.removeEventListener("afterprint", restoreAfterPrint);
    console.log("✅ Print completed, isolated view restored");
  };

  window.addEventListener("afterprint", restoreAfterPrint);

  setTimeout(() => {
    window.print();
  }, 100);
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("✅ Forms Carousel logic initialized");

  // Check Bootstrap
  if (typeof bootstrap === "undefined") {
    console.error("❌ Bootstrap not loaded");
  } else {
    console.log("✅ Bootstrap carousel ready");
  }

  // Check html2pdf
  if (typeof html2pdf === "undefined") {
    console.error("❌ html2pdf not loaded");
  } else {
    console.log("✅ html2pdf ready");
  }
});
