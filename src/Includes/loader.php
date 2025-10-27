<!-- includes/loader.php -->
<div id="loader-wrapper">
  <div class="loader-container">
    <img src="public/images/Nara logo.png" alt="Logo" class="loader-logo">
    <div class="bars">
      <div class="bar bar1"></div>
      <div class="bar bar2"></div>
      <div class="bar bar3"></div>
      <div class="bar bar4"></div>
      <div class="bar bar5"></div>
      <div class="bar bar6"></div>
    </div>
  </div>
</div>

<style>
/* ===== Loader Styles ===== */
#loader-wrapper {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: #ffffff;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition: opacity 0.5s ease, visibility 0.5s ease;
}

.loader-container {
  text-align: center;
}

.loader-logo {
  width: 200px;
  height: auto;
  margin-bottom: 30px;
}

.bars {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 8px;
}

.bar {
  width: 8px;
  height: 40px;
  background: #007bff;
  border-radius: 5px;
  animation: bounce 1s infinite ease-in-out;
}

.bar2 { animation-delay: 0.1s; }
.bar3 { animation-delay: 0.2s; }
.bar4 { animation-delay: 0.3s; }
.bar5 { animation-delay: 0.4s; }
.bar6 { animation-delay: 0.5s; }

@keyframes bounce {
  0%, 100% { transform: scaleY(0.4); }
  50% { transform: scaleY(1); }
}

/* Hidden after load */
#loader-wrapper.hidden {
  opacity: 0;
  visibility: hidden;
}
</style>
