<?php
// includes/footer.php
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const menu = document.querySelector('.sidebar-wrapper .sidebar-menu');
  if (!menu) return;

  // buat indikator
  let indicator = menu.querySelector('.mc-nav-indicator');
  if (!indicator) {
    indicator = document.createElement('div');
    indicator.className = 'mc-nav-indicator';
    menu.style.position = 'relative';
    menu.prepend(indicator);
  }

  function moveIndicatorTo(el){
    if (!el) { indicator.style.opacity = 0; return; }
    const menuRect = menu.getBoundingClientRect();
    const rect = el.getBoundingClientRect();
    const top = rect.top - menuRect.top;
    indicator.style.height = rect.height + 'px';
    indicator.style.transform = `translateY(${top}px)`;
    indicator.style.opacity = 1;
  }

  function getActive(){
    return menu.querySelector('.nav-link.active') || menu.querySelector('.nav-link');
  }

  // initial
  const active = getActive();
  if (active) moveIndicatorTo(active);

  // hover follow (biar interaktif)
  menu.querySelectorAll('.nav-link').forEach(a => {
    a.addEventListener('mouseenter', () => moveIndicatorTo(a), { passive:true });
    a.addEventListener('mouseleave', () => moveIndicatorTo(getActive()), { passive:true });
  });

  // update on resize
  window.addEventListener('resize', () => moveIndicatorTo(getActive()), { passive:true });
})();
</script>

</body>
</html>
