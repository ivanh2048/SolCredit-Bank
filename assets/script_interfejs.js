window.onload = function() {
    const savedTheme = localStorage.getItem('theme') || 'dark-theme';
    document.body.classList.add(savedTheme);

    const themeIcon = document.getElementById('theme-icon');
    if (savedTheme === 'light-theme') {
        themeIcon.src = '/image/moon.png';
    } else {
        themeIcon.src = '/image/sunny.png';
    }

    const elements = {
        container: document.querySelector('.container'),
        labels: document.querySelectorAll('label'),
        inputs: document.querySelectorAll('input'),
        buttons: document.querySelectorAll('button'),
        messages: document.querySelectorAll('.message'),
        links: document.querySelectorAll('a')
    };

    Object.keys(elements).forEach(key => {
        elements[key].forEach(el => el.classList.add(savedTheme));
    });
}

document.getElementById('theme-toggle').addEventListener('click', function () {
    const body = document.body;
    const themeIcon = document.getElementById('theme-icon');
    const currentTheme = body.classList.contains('dark-theme') ? 'light-theme' : 'dark-theme';
    
    body.classList.toggle('light-theme');
    body.classList.toggle('dark-theme');

    if (currentTheme === 'light-theme') {
        themeIcon.src = '/image/moon.png';
    } else {
        themeIcon.src = '/image/sunny.png';
    }

    localStorage.setItem('theme', currentTheme);
});
