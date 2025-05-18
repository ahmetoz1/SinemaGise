</div> <!-- admin-content kapanışı -->
    </div> <!-- admin-container kapanışı -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Tarih ve saat inputları için otomatik formatlama
        document.addEventListener('DOMContentLoaded', function() {
            // Film ekleme formundaki tarih alanları
            const giseBaslangic = document.getElementById('gise_baslangıc');
            const giseBitis = document.getElementById('gise_bitis');
            
            if(giseBaslangic && giseBitis) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                
                const defaultValue = `${year}-${month}-${day}T${hours}:${minutes}`;
                giseBaslangic.value = defaultValue;
                
                // Varsayılan olarak 2 hafta sonrası
                const endDate = new Date(now);
                endDate.setDate(endDate.getDate() + 14);
                const endYear = endDate.getFullYear();
                const endMonth = String(endDate.getMonth() + 1).padStart(2, '0');
                const endDay = String(endDate.getDate()).padStart(2, '0');
                
                giseBitis.value = `${endYear}-${endMonth}-${endDay}T${hours}:${minutes}`;
            }

            // Data table row hover effect
            $('.data-table tbody tr').hover(
                function() {
                    $(this).css('background-color', 'rgba(108, 92, 231, 0.05)');
                },
                function() {
                    $(this).css('background-color', '');
                }
            );
        });
    </script>
</body>
</html>