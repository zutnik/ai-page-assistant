const colorInput = document.querySelector('#ai-primary-color');

if (colorInput) {
  colorInput.addEventListener('input', () => {
    document.documentElement.style.setProperty('--ai-pa-primary', colorInput.value);
  });
}
