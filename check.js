function addGeneratedKey(arr, afterKey = null) {
    const newKey = crypto.randomUUID(); // Генерируем новый UUID

    return arr.map(obj => {
        // Если ключ указан и есть в объекте
        if (afterKey && obj.hasOwnProperty(afterKey)) {
            const newObj = {};
            let inserted = false;

            // Вставляем новый ключ после указанного
            for (const key in obj) {
                newObj[key] = obj[key];
                if (key === afterKey) {
                    newObj[newKey] = ""; // Вставляем новый ключ
                    inserted = true;
                }
            }

            // Если ключ найден, возвращаем новый объект
            return inserted ? newObj : { ...obj, [newKey]: "" };
        }

        // Если ключ не указан или его нет в объекте, просто добавляем новый ключ в конец
        obj[newKey] = "";
        return obj;
    });
}

function removeKeyFromArray(arr, keyToRemove) {
    return arr.map(obj => {
        if (obj.hasOwnProperty(keyToRemove)) {
            const newObj = { ...obj }; // Копируем объект
            delete newObj[keyToRemove]; // Удаляем ключ
            return newObj;
        }
        return obj; // Если ключа нет, возвращаем объект без изменений
    });
}

function removeRowByIndex(jsonArray, index) {
    if (index < 0 || index >= jsonArray.length) {
        console.error("Ошибка: индекс выходит за границы массива.");
        return jsonArray; // Возвращаем без изменений
    }
    
    jsonArray.splice(index, 1); // Удаляем элемент по индексу
    return jsonArray;
}

function addEmptyRecord(arr, index = arr.length) {
    // Собираем все уникальные ключи из массива
    const allKeys = new Set();
    arr.forEach(obj => {
        Object.keys(obj).forEach(key => allKeys.add(key));
    });

    // Создаём новый объект с этими ключами и пустыми значениями
    const newRecord = {};
    allKeys.forEach(key => newRecord[key] = "");

    // Определяем, куда вставить (по индексу или в конец)
    if (index >= 0 && index < arr.length) {
        arr.splice(index + 1, 0, newRecord); // Вставляем после указанного индекса
    } else {
        arr.push(newRecord); // Если индекс некорректный — добавляем в конец
    }

    return arr;
}

function jsonToTable(json) {
    // Собираем все уникальные UUID-ключи
    const allKeys = new Set();
    json.forEach(obj => Object.keys(obj).forEach(key => allKeys.add(key)));
    const keys = Array.from(allKeys); // Гарантируем одинаковый порядок колонок

    let html = `<table class="table table-bordered">`;

    json.forEach((obj, index) => {
        html += `<tr id="row-${index}">`;

        keys.forEach(key => {
            html += `<td data-key="${key}">
                        <span>${obj[key] !== undefined ? obj[key] : ""}</span>
                        <button onclick="checkTable('${key}')">Click</button>
                     </td>`;
        });

        html += `</tr>`;
    });

    html += `</table>`;
    console.log(html);
    return html;
}

function tableToJson(table) {
    const json = [];

    // Перебираем все строки таблицы
    for (let row of table.rows) {
        const obj = {};
        for (let i = 0; i < row.cells.length; i++) {
            obj[`col_${i}`] = row.cells[i].textContent.trim(); // Даем колонкам имена col_0, col_1...
        }
        json.push(obj);
    }

    return json;
}

// Исходный массив
const data = [
    {
        "6fa459ea-ee8a-3ca4-894e-db77e160355e": 924,
        "550e8400-e29b-41d4-a716-446655440000": 482
    },
    {
        "6fa459ea-ee8a-3ca4-894e-db77e160355e": 921,
        "550e8400-e29b-41d4-a716-446655440000": 487,
    }
];

// Добавляем новую запись после индекса 0


const html = jsonToTable(data);

console.table(html);

