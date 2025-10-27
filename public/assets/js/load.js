// assets/js/loader.js

window.addEventListener("load", function() {
  const loader = document.getElementById("loader-wrapper");
  if (loader) {
    const minDisplayTime = 2000; // Minimum time in milliseconds (2 seconds)
    const startTime = performance.timing.navigationStart;
    const now = Date.now();
    const timeElapsed = now - startTime;
    const remainingTime = Math.max(0, minDisplayTime - timeElapsed);

    setTimeout(() => {
      loader.classList.add("hidden");
    }, remainingTime);
  }
});
