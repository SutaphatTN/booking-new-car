window.AppSpinner = {
  show() {
    document.getElementById('appSpinner')?.classList.remove('d-none');
  },
  hide() {
    document.getElementById('appSpinner')?.classList.add('d-none');
  }
};
