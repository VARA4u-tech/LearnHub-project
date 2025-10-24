        <!-- Footer -->
        <footer class="flex justify-center">
            <div class="flex max-w-[960px] flex-1 flex-col">
                <footer class="flex flex-col gap-6 px-5 py-10 text-center @container">
                    <div class="flex flex-wrap items-center justify-center gap-6 @[480px]:flex-row @[480px]:justify-around">
                        <a class="text-[#adadad] text-base font-normal leading-normal min-w-40" href="index.php">About</a>
                        <a class="text-[#adadad] text-base font-normal leading-normal min-w-40" href="admin_blog.php">Contact</a>
                        
                        <a class="text-[#adadad] text-base font-normal leading-normal min-w-40" href="privacy_policy.php">Privacy Policy</a>
                    </div>
                    <p class="text-[#adadad] text-base font-normal leading-normal">© 2025 A LearnHub Community. Have Fun Folks.</p>
                </footer>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            const likeButtons = document.querySelectorAll('.like-btn');

            likeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const noteId = this.dataset.noteId;
                    const formData = new FormData();
                    formData.append('note_id', noteId);
                    formData.append('csrf_token', csrfToken);

                    fetch('like_note.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const likeCountSpan = this.querySelector('.like-count');
                            likeCountSpan.textContent = data.like_count;

                            if (data.liked) {
                                this.classList.add('text-red-500');
                                this.classList.remove('text-gray-500');
                            } else {
                                this.classList.remove('text-red-500');
                                this.classList.add('text-gray-500');
                            }
                        } else {
                            if (data.message === 'You must be logged in to like a note.') {
                                window.location.href = 'login.php';
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });

            const bookmarkButtons = document.querySelectorAll('.bookmark-btn');

            bookmarkButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const noteId = this.dataset.noteId;
                    const formData = new FormData();
                    formData.append('note_id', noteId);
                    formData.append('csrf_token', csrfToken);

                    fetch('bookmark_note.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.bookmarked) {
                                this.classList.add('text-blue-500');
                                this.classList.remove('text-gray-500');
                            } else {
                                this.classList.remove('text-blue-500');
                                this.classList.add('text-gray-500');
                            }
                        } else {
                            if (data.message === 'You must be logged in to bookmark a note.') {
                                window.location.href = 'login.php';
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>
</html>