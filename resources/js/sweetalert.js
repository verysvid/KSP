import Swal from 'sweetalert2';

window.Swal = Swal;

window.swalSuccess = function (message, title = 'Berhasil') {
    return Swal.fire({
        icon: 'success',
        title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#4f46e5',
    });
};

window.swalError = function (message, title = 'Terjadi Kesalahan') {
    return Swal.fire({
        icon: 'error',
        title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc2626',
    });
};

window.swalConfirm = function (options = {}) {
    return Swal.fire({
        icon: options.icon ?? 'warning',
        title: options.title ?? 'Konfirmasi',
        text: options.text,
        html: options.html,
        showCancelButton: options.showCancelButton ?? true,
        confirmButtonText: options.confirmButtonText ?? 'Ya, Lanjutkan',
        cancelButtonText: options.cancelButtonText ?? 'Batal',
        confirmButtonColor: options.confirmButtonColor ?? '#4f46e5',
        cancelButtonColor: options.cancelButtonColor ?? '#64748b',
        reverseButtons: true,
        focusCancel: true,
    });
};

export { Swal };
