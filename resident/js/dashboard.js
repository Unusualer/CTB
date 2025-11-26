/**
 * Dashboard Enhancement Script
 * Adds animations and dynamic elements to the dashboard
 */
document.addEventListener('DOMContentLoaded', function () {

    // Remove the particle background call
    // createParticleBackground();

    // Counter Animation for Stat Numbers
    const animateCounter = (element, target, duration = 1500) => {
        let start = 0;
        const startTime = performance.now();

        const updateCounter = (currentTime) => {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);

            // Easing function for smoother animation
            const easeOutQuart = progress => 1 - Math.pow(1 - progress, 4);
            const easedProgress = easeOutQuart(progress);

            const currentValue = Math.floor(easedProgress * target);

            if (target > 1000) {
                element.textContent = currentValue.toLocaleString();
            } else {
                element.textContent = currentValue;
            }

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                // Ensure final value is exact
                if (target > 1000) {
                    element.textContent = target.toLocaleString();
                } else {
                    element.textContent = target;
                }
            }
        };

        requestAnimationFrame(updateCounter);
    };

    // Create dynamic particle background
    function createParticleBackground() {
        const dashboardContent = document.querySelector('.dashboard-content');
        if (!dashboardContent) return;

        // Create the canvas element for particles
        const canvas = document.createElement('canvas');
        canvas.id = 'particleCanvas';
        canvas.style.cssText = 'position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:0;';
        dashboardContent.prepend(canvas);

        // Get the canvas context
        const ctx = canvas.getContext('2d');

        // Set canvas dimensions
        const resizeCanvas = () => {
            canvas.width = dashboardContent.offsetWidth;
            canvas.height = dashboardContent.offsetHeight;
        };

        // Initial canvas sizing
        resizeCanvas();

        // Resize canvas when window changes
        window.addEventListener('resize', resizeCanvas);

        // Particle properties
        const particles = [];
        const particleCount = 30;
        const colors = [
            'rgba(78, 115, 223, 0.2)',  // primary
            'rgba(28, 200, 138, 0.2)',  // success
            'rgba(54, 185, 204, 0.2)',  // info
            'rgba(246, 194, 62, 0.2)'   // warning
        ];

        // Create initial particles
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 5 + 1,
                color: colors[Math.floor(Math.random() * colors.length)],
                speed: 0.1,
                directionX: Math.random() * 0.4 - 0.2,
                directionY: Math.random() * 0.4 - 0.2,
                opacity: Math.random() * 0.5 + 0.3
            });
        }

        // Animation function
        function animateParticles() {
            // Clear the canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Update and draw particles
            particles.forEach(particle => {
                // Move the particle
                particle.x += particle.directionX;
                particle.y += particle.directionY;

                // Reverse direction if hitting the edge
                if (particle.x < 0 || particle.x > canvas.width) {
                    particle.directionX *= -1;
                }

                if (particle.y < 0 || particle.y > canvas.height) {
                    particle.directionY *= -1;
                }

                // Draw the particle
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
                ctx.fillStyle = particle.color;
                ctx.fill();
            });

            // Request the next frame
            requestAnimationFrame(animateParticles);
        }

        // Start the animation
        animateParticles();
    }

    // Get all stat number elements
    const statNumbers = document.querySelectorAll('.stat-number');

    // Use Intersection Observer for triggering animations when visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;

                // Extract number from content (removing currency symbols, commas, etc)
                const content = element.textContent;
                const numericValue = parseFloat(
                    content.replace(/[^0-9.-]+/g, '')
                );

                // Animate the counter
                element.textContent = '0';
                animateCounter(element, numericValue);

                // Unobserve after animation is triggered
                observer.unobserve(element);
            }
        });
    }, { threshold: 0.1 });

    // Observe each stat number element
    statNumbers.forEach(statNumber => {
        observer.observe(statNumber);
    });

    // Maintenance circles counter animation
    const statCircleCounts = document.querySelectorAll('.stat-circle .count');

    statCircleCounts.forEach(countElement => {
        observer.observe(countElement);
    });

    // Card hover effects
    const cards = document.querySelectorAll('.stat-card, .chart-card, .content-card, .maintenance-card');

    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = 'var(--shadow-lg)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
    });

    // Activity and Ticket items hover effect
    const listItems = document.querySelectorAll('.activity-item, .ticket-item');

    listItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.transform = 'translateX(10px)';
            this.style.backgroundColor = 'rgba(var(--card-bg-rgb), 0.9)';
        });

        item.addEventListener('mouseleave', function () {
            this.style.transform = 'translateX(0)';
            this.style.backgroundColor = 'rgba(var(--card-bg-rgb), 0.7)';
        });
    });

    // Set up refresh button for each chart card
    const setupChartRefresh = () => {
        const chartCards = document.querySelectorAll('.chart-card');

        chartCards.forEach(card => {
            const actionButton = card.querySelector('.btn-icon');

            if (actionButton) {
                // Create dropdown menu for the button
                const dropdown = document.createElement('div');
                dropdown.className = 'chart-dropdown';
                dropdown.innerHTML = `
                    <ul>
                        <li data-action="refresh"><i class="fas fa-sync-alt"></i> Actualiser</li>
                        <li data-action="download"><i class="fas fa-download"></i> Télécharger</li>
                        <li data-action="fullscreen"><i class="fas fa-expand"></i> Plein écran</li>
                    </ul>
                `;

                dropdown.style.position = 'absolute';
                dropdown.style.right = '0';
                dropdown.style.top = '100%';
                dropdown.style.backgroundColor = 'var(--card-bg)';
                dropdown.style.borderRadius = '0.5rem';
                dropdown.style.boxShadow = 'var(--shadow-md)';
                dropdown.style.padding = '0.5rem 0';
                dropdown.style.zIndex = '10';
                dropdown.style.display = 'none';
                dropdown.style.minWidth = '160px';

                const dropdownStyles = `
                    .chart-dropdown ul {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                    }
                    
                    .chart-dropdown li {
                        padding: 0.5rem 1rem;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        transition: background-color 0.2s;
                        font-size: 0.9rem;
                    }
                    
                    .chart-dropdown li:hover {
                        background-color: var(--sidebar-hover);
                    }
                    
                    .chart-dropdown i {
                        width: 1rem;
                        text-align: center;
                    }
                `;

                const styleElement = document.createElement('style');
                styleElement.textContent = dropdownStyles;
                document.head.appendChild(styleElement);

                // Append dropdown to the chart header
                const chartHeader = card.querySelector('.chart-header');
                chartHeader.style.position = 'relative';
                chartHeader.appendChild(dropdown);

                // Toggle dropdown on button click
                actionButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    dropdown.style.display = 'none';
                });

                // Handle dropdown actions
                dropdown.addEventListener('click', (e) => {
                    const action = e.target.closest('li')?.dataset.action;

                    if (action === 'refresh') {
                        const canvas = card.querySelector('canvas');
                        if (canvas) {
                            const chart = Chart.getChart(canvas);
                            if (chart) {
                                // Add refresh animation
                                actionButton.querySelector('i').classList.add('fa-spin');
                                setTimeout(() => {
                                    chart.update();
                                    actionButton.querySelector('i').classList.remove('fa-spin');
                                }, 1000);
                            }
                        }
                    } else if (action === 'download') {
                        const canvas = card.querySelector('canvas');
                        if (canvas) {
                            const image = canvas.toDataURL('image/png');
                            const link = document.createElement('a');
                            link.download = 'chart-export.png';
                            link.href = image;
                            link.click();
                        }
                    } else if (action === 'fullscreen') {
                        const chartBody = card.querySelector('.chart-body');
                        if (chartBody) {
                            if (document.fullscreenElement) {
                                document.exitFullscreen();
                            } else {
                                chartBody.requestFullscreen();
                            }
                        }
                    }

                    dropdown.style.display = 'none';
                });
            }
        });
    };

    setupChartRefresh();

    // Report Button Click Effect
    const reportBtn = document.querySelector('.page-header .btn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function () {
            this.classList.add('btn-loading');
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

            setTimeout(() => {
                this.classList.remove('btn-loading');
                this.innerHTML = '<i class="fas fa-check"></i> Rapport Généré';

                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-download"></i> Générer un Rapport';
                }, 2000);
            }, 1500);
        });
    }
});
