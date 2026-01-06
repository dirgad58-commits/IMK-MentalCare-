// assets/js/landing.js
// HCI: status jelas (active nav), tab aksesibel, error prevention, back-to-top, char counter.

document.addEventListener('DOMContentLoaded', () => {
  // ===== Active nav (IntersectionObserver) + aria-current
  const navLinks = [...document.querySelectorAll('nav a[href^="#"]')];
  const pairs = navLinks
    .map(a => ({ a, sec: document.querySelector(a.getAttribute('href')) }))
    .filter(x => x.sec);

  function setActive(link){
    navLinks.forEach(a => {
      a.classList.remove('active');
      a.removeAttribute('aria-current');
    });
    link.classList.add('active');
    link.setAttribute('aria-current', 'page');
  }

  // Click: smooth scroll + update hash
  navLinks.forEach(a => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (!target) return;

      e.preventDefault();
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      history.replaceState(null, '', a.getAttribute('href'));
      setActive(a);
    });
  });

  if (pairs.length){
    const offset = 100;
    const io = new IntersectionObserver((entries) => {
      const visible = entries
        .filter(en => en.isIntersecting)
        .sort((x, y) => (y.intersectionRatio - x.intersectionRatio))[0];

      if (!visible) return;
      const found = pairs.find(p => p.sec === visible.target);
      if (found) setActive(found.a);
    }, {
      threshold: [0.2, 0.35, 0.5, 0.65],
      rootMargin: `-${offset}px 0px -55% 0px`
    });

    pairs.forEach(p => io.observe(p.sec));
  }

  // Initial
  const initial = navLinks.find(a => a.getAttribute('href') === location.hash);
  if (initial) setActive(initial);
  else if (navLinks[0]) setActive(navLinks[0]);

  // ===== Tabs: pesan / report (HCI: kontrol + aksesibilitas)
  const tabPesanBtn = document.getElementById('tabPesanBtn');
  const tabReportBtn = document.getElementById('tabReportBtn');
  const tabPesan = document.getElementById('tabPesan');
  const tabReport = document.getElementById('tabReport');
  const openReportLink = document.getElementById('openReportLink');

  function showPesan(){
    if (!tabPesan || !tabReport) return;
    tabPesan.hidden = false;
    tabReport.hidden = true;

    tabPesanBtn?.setAttribute('aria-selected', 'true');
    tabReportBtn?.setAttribute('aria-selected', 'false');

    tabPesanBtn?.classList.add('btn-brand');
    tabPesanBtn?.classList.remove('btn-outline-brand');

    tabReportBtn?.classList.add('btn-outline-danger');
    tabReportBtn?.classList.remove('btn-danger');
  }

  function showReport(){
    if (!tabPesan || !tabReport) return;
    tabPesan.hidden = true;
    tabReport.hidden = false;

    tabPesanBtn?.setAttribute('aria-selected', 'false');
    tabReportBtn?.setAttribute('aria-selected', 'true');

    tabPesanBtn?.classList.remove('btn-brand');
    tabPesanBtn?.classList.add('btn-outline-brand');

    tabReportBtn?.classList.remove('btn-outline-danger');
    tabReportBtn?.classList.add('btn-danger');
  }

  tabPesanBtn?.addEventListener('click', showPesan);
  tabReportBtn?.addEventListener('click', showReport);
  openReportLink?.addEventListener('click', () => showReport());

  // ===== Report validation: minimal ID atau URL (HCI: error prevention)
  const form = document.getElementById('reportForm');
  const box  = document.getElementById('reportValidation');
  const idEl = document.getElementById('target_id');
  const urlEl = document.getElementById('target_url');

  function hideBox(){
    if (!box) return;
    box.classList.add('d-none');
    box.textContent = '';
  }

  idEl?.addEventListener('input', hideBox);
  urlEl?.addEventListener('input', hideBox);

  form?.addEventListener('submit', (e) => {
    const id = (idEl?.value || '').trim();
    const url = (urlEl?.value || '').trim();
    if (!id && !url){
      e.preventDefault();
      if (box){
        box.textContent = 'Isi minimal salah satu: ID Konten atau URL.';
        box.classList.remove('d-none');
      }
      idEl?.focus();
    }
  });

  // ===== Char counter (HCI: recognition > recall)
  document.querySelectorAll('.char-counter[data-for]').forEach(counter => {
    const id = counter.getAttribute('data-for');
    const el = document.getElementById(id);
    if (!el) return;

    const max = el.getAttribute('maxlength') ? parseInt(el.getAttribute('maxlength'), 10) : null;

    const render = () => {
      const len = (el.value || '').length;
      counter.textContent = max ? `${len}/${max} karakter` : `${len} karakter`;
    };

    el.addEventListener('input', render);
    render();
  });

  // ===== Back to top (HCI: kontrol + efisiensi)
  const backToTop = document.getElementById('backToTop');
  if (backToTop){
    const toggle = () => backToTop.classList.toggle('show', window.scrollY > 600);
    toggle();
    window.addEventListener('scroll', toggle, { passive: true });

    backToTop.addEventListener('click', () => {
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }
});
