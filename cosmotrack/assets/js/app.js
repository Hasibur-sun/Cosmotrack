document.addEventListener('DOMContentLoaded', () => {

  // Mobile sidebar toggle
  const btn  = document.querySelector('.mobile-menu');
  const side = document.querySelector('.sidebar');
  if (btn && side) {
    btn.onclick = () => side.classList.toggle('open');
    document.addEventListener('click', e => {
      if (side.classList.contains('open') &&
          !side.contains(e.target) &&
          !btn.contains(e.target)) {
        side.classList.remove('open');
      }
    });
  }

  // Active sidebar link highlight
  const page = window.location.pathname.split('/').pop();
  document.querySelectorAll('.sidebar nav a').forEach(a => {
    if (a.getAttribute('href') === page) {
      a.classList.add('active');
    }
  });

});