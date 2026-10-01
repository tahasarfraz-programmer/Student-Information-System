document.getElementById('burger')?.addEventListener('click', () => document.getElementById('side').classList.toggle('open'));
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
  if (!confirm(f.dataset.confirm)) e.preventDefault();
}));
document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.classList.add('gone'), 4000));
document.querySelectorAll('input[type=file][data-preview]').forEach(i => i.addEventListener('change', () => {
  const img = document.getElementById(i.dataset.preview);
  if (i.files[0] && img) { img.src = URL.createObjectURL(i.files[0]); img.hidden = false; }
}));
document.querySelectorAll('[data-mark-all]').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('input[type=radio][value=' + b.dataset.markAll + ']').forEach(r => r.checked = true);
}));
