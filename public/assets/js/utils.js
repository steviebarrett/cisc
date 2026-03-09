export function getJsonScript(id) {
    const el = document.getElementById(id);
    if (!el) return null;

    try {
        return JSON.parse(el.textContent || 'null');
    } catch (err) {
        console.error(`Invalid JSON in script#${id}`, err);
        return null;
    }
}
