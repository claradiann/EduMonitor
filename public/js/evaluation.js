const scoreLabels = {
    1: 'Sangat Kurang',
    2: 'Kurang',
    3: 'Cukup',
    4: 'Baik',
    5: 'Sangat Baik'
};

function setRating(indicatorId, score) {
    document.getElementById(`rating_${indicatorId}_${score}`).checked = true;

    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById(`star_${indicatorId}_${i}`);
        if (i <= score) {
            star.classList.add('star-active');
            star.classList.remove('text-slate-200');
        } else {
            star.classList.remove('star-active');
            star.classList.add('text-slate-200');
        }
    }

    const label = document.getElementById(`rating_label_${indicatorId}`);
    label.innerText = scoreLabels[score];
    label.className = "text-xs font-bold text-amber-500 ml-4 uppercase tracking-wider";
}