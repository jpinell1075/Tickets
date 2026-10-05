<script setup>
    import { onMounted, onUnmounted, ref } from 'vue';

    let mouseX = 0;
    let mouseY = 0;
    const selectors = ['a', 'button', '.btn', 'input', 'select', 'label', '.alert'];
    let lastActive = null;
    let active = ref(window.localStorage.getItem('highlight') === 'true' || false);
    let clickAsistActive = ref(window.localStorage.getItem('clickAsist') === 'true' || false);

    window.addEventListener('config-updated', (event) => {
        let highlight = window.localStorage.getItem('highlight') === 'true';
        let clickAsist = window.localStorage.getItem('clickAsist') === 'true';

        active.value = highlight;
        clickAsistActive.value = clickAsist;

        if(!active.value) {
            if (lastActive) lastActive.classList.remove('premonish-active');
            lastActive = null;
        }
    });

    function getDistanceToMouse(el) {
        const rect = el.getBoundingClientRect();
        const elX = rect.left + rect.width / 2;
        const elY = rect.top + rect.height / 2;
        return Math.sqrt((elX - mouseX) ** 2 + (elY - mouseY) ** 2);
    }

    function getSelectors() {
        return selectors.map(selector => `${selector}:not([disabled]):not(.disabled)`).join(',');
    }

    function highlightClosest() {
        if(!active.value) return;
        const candidates = Array.from(document.querySelectorAll(getSelectors()));
        if (candidates.length === 0) return;

        candidates.forEach(el => el.classList.remove('premonish-active'));

        let maxZ = Math.max(
            ...candidates.map(el => {
                const z = window.getComputedStyle(el).zIndex;
                return isNaN(Number(z)) ? 0 : Number(z);
            })
        );

        const topCandidates = candidates.filter(el => {
            const z = window.getComputedStyle(el).zIndex;
            return (isNaN(Number(z)) ? 0 : Number(z)) === maxZ;
        });

        let closest = topCandidates[0];
        let minDist = getDistanceToMouse(closest);
        for (let i = 1; i < topCandidates.length; i++) {
            const dist = getDistanceToMouse(topCandidates[i]);
            if (dist < minDist) {
                minDist = dist;
                closest = topCandidates[i];
            }
        }
        closest.classList.add('premonish-active');
        lastActive = closest;
    }

    function onMouseMove(e) {
        mouseX = e.clientX;
        mouseY = e.clientY;
        highlightClosest();
    }

    onMounted(() => {
        document.addEventListener('mousemove', onMouseMove);

        document.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;

            if (!clickAsistActive.value) return;

            let activeEl = document.querySelector('.premonish-active');
            if (!activeEl) return;

            if (e.target === activeEl) return;

            if( activeEl.tagName === 'LABEL' && activeEl.htmlFor) {
                const forEl = document.getElementById(activeEl.htmlFor);
                if (forEl) {
                    activeEl = forEl;
                }
            }

            activeEl.click();
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        }, true);
    });

    onUnmounted(() => {
        document.removeEventListener('mousemove', onMouseMove);
        if (lastActive) lastActive.classList.remove('premonish-active');
    });
</script>
<template></template>
<style>
    .premonish-active {
        --color: var(--color-green-500);
        outline: 4px solid var(--color);
    }
</style>