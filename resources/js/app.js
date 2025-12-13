import './bootstrap';
/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);

// laravel style -->
import '../assets/vendor/js/helpers';

// ? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. 
import '../assets/js/config';

import Swal from 'sweetalert2';
window.Swal = Swal;