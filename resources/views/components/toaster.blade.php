@if (count($toasts))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @json($toasts).forEach(function (item) {
                window.toast(item.message, item.type);
            });
        });
    </script>
@endif
