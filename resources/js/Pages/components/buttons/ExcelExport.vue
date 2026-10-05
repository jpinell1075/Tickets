// This script defines an Excel export button component.
// It triggers the export of a table's data to an Excel file.
<script setup>
    import { onMounted } from 'vue';

    const props = defineProps({
        filename: String,
        target: String,
    });

    onMounted(() => {
        const btn = document.getElementById('excel_api_export_' + props.target);
        if (btn) {
            btn.addEventListener('click', function() {
                apiExportToExcel(btn.dataset.target, btn.dataset.filename);
            });
        }
    });
</script>

<template>
    <button aria-label="exportar a excel"
        type="button" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded transition-colors excel_api_export" 
        :data-filename="filename" 
        :data-target="target" 
        :id="'excel_api_export_'+props.target">
        <i class='bx bxs-file-export'></i>
    </button>
</template>

<script>
    function removeDisplayNone(table){
        let table_whitout_display_none = table.cloneNode(true);

        table_whitout_display_none.querySelectorAll('*').forEach(function(element){
            if(element.style.display === 'none' || element.classList.contains('d-none')){
                element.remove();
            }
        });

        let table_html_whitout_display_none = table_whitout_display_none.innerHTML;
        table_whitout_display_none.remove();

        return table_html_whitout_display_none;
    }

    function removeOptionsColumn(table) {
        let table_without_options = table.cloneNode(true);

        let options_column_index = -1;
        table_without_options.querySelectorAll('th').forEach(function(th, index) {
            if (th.innerText.trim() === 'Opciones') {
                options_column_index = index;
            }
        });

        if (options_column_index !== -1) {
            table_without_options.querySelectorAll('tr').forEach(function(tr) {
                let cells = tr.querySelectorAll('td, th');
                if (cells[options_column_index]) {
                    cells[options_column_index].remove();
                }
            });
        }

        let table_html_without_options = table_without_options.innerHTML;
        table_without_options.remove();

        return table_html_without_options;
    }

    function setClassIntoElementText(table){
        let table_whith_class = table.cloneNode(true);

        table_whith_class.querySelectorAll('td, th').forEach(function(element){
            let element_class = element.dataset.excelClass;
            if(element_class){
                element.innerText = "{" + element_class + "}" + element.innerText;
            }
        });

        let table_html_whith_class = table_whith_class.innerHTML;
        table_whith_class.remove();

        return table_html_whith_class;
    }

    function apiExportToExcel(table_id, file_name = 'Exportación'){
        const table = document.querySelector('table#' + table_id);
        const url = api_url + 'exports/excel';

        let new_table = table.cloneNode(true);

        new_table.innerHTML = removeDisplayNone(new_table);
        new_table.innerHTML = removeOptionsColumn(new_table);
        new_table.innerHTML = setClassIntoElementText(new_table);

        let table_html = new_table.innerHTML;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                table_html: table_html,
                file_name: file_name,
            })
        }).then(response => {
            if (response.ok) {
                return response.blob();
            }
            else {
                showToast('Error al exportar a Excel');
            }
        }).then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${file_name}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
            window.URL.revokeObjectURL(url);
        }).catch(error => {
            showToast('Error al exportar a Excel');
        });
    }
</script>