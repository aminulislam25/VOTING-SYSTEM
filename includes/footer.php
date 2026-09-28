            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle dropdown menu
        $('.nav-dropdown > a').click(function(e) {
            e.preventDefault();
            $(this).siblings('.dropdown-content').slideToggle();
            $(this).find('.fa-chevron-down').toggleClass('rotate');
        });

        // Active menu item
        const currentPath = window.location.pathname;
        $('.nav-item').each(function() {
            if ($(this).attr('href') && currentPath.includes($(this).attr('href'))) {
                $(this).addClass('active');
            }
        });
    </script>
</body>
</html> 