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

// Core
import '../assets/vendor/libs/jquery/jquery';
import '../assets/vendor/libs/popper/popper';
import '../assets/vendor/js/bootstrap';

// Plugins
import '../assets/vendor/libs/datatables/datatables.min';
import '../assets/vendor/libs/select2/select2.min';
import '../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar';
import '../assets/vendor/js/menu';

// App
import '../assets/js/main';

import Swal from 'sweetalert2';
window.Swal = Swal;
