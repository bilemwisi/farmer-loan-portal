document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('farmerForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    const amount = Number(form.amount.value || 0);
    if (!amount || amount <= 0) {
      e.preventDefault();
      alert('Please enter a valid loan amount.');
      return;
    }

    const fileInput = form.querySelector('input[name="nid_file"]');
    if (fileInput && fileInput.files && fileInput.files.length) {
      const file = fileInput.files[0];
      const maxMB = 5;
      if (file.size > maxMB * 1024 * 1024) {
        e.preventDefault();
        alert('NID image must be smaller than ' + maxMB + ' MB.');
        return;
      }
      if (!file.type.startsWith('image/')) {
        e.preventDefault();
        alert('NID file must be an image.');
        return;
      }
    }
  });
});
