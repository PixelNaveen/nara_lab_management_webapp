document.addEventListener("DOMContentLoaded", function() {
    const loader = document.getElementById("loader-wrapper");
    if (!loader) return;

    const minDisplayTime = 1500;
    const startTime = performance.timing.navigationStart;
    const elapsed = Date.now() - startTime;
    const delay = Math.max(0, minDisplayTime - elapsed);

    setTimeout(() => {
        loader.classList.add("hidden");
        setTimeout(() => { loader.style.display = 'none'; }, 600);
    }, delay);
});