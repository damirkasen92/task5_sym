export function readQueryDataJson() {
    return JSON.parse(document.getElementById('query_data').textContent) || {};
}

export function setParamDataJson(key, val) {
    let data = readQueryDataJson()[key] = val;
    document.getElementById('query_data').textContent = JSON.stringify(data);
}

export function removeParamDataJson(key) {
    let data = delete readQueryDataJson()[key];
    document.getElementById('query_data').textContent = JSON.stringify(data);
}
