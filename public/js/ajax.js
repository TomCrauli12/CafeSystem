document.addEventListener('DOMContentLoaded', function(){

    const ajaxUrl = '/Cafe/app/Controlers/AjaxController.php';

    function escapeHtml(value){

        const element = document.createElement('div');

        element.textContent = String(value ?? '');

        return element.innerHTML;
    }

    function showMessage(message, isError=false){

        const container = document.getElementById('ajax-message');

        if(!container || !message){

            return;
        }

        container.innerHTML = '<p>' + escapeHtml(message) + '</p>';
        container.dataset.type = isError ? 'error' : 'success';
    }

    async function request(url, options={}){

        options.headers = Object.assign({}, options.headers, {'X-Requested-With':'XMLHttpRequest'});

        const response = await fetch(url, options);

        if(response.redirected){

            window.location.href = response.url;

            return null;
        }

        const contentType = response.headers.get('content-type') || '';

        if(!contentType.includes('application/json')){

            throw new Error('Сервер вернул неправильный ответ');
        }

        const result = await response.json();

        if(!response.ok || !result.success){

            throw new Error(result.message || 'Не удалось выполнить действие');
        }

        return result;
    }

    async function sendForm(form){

        const result = await request(form.action, {
            method:form.method || 'POST',
            body:new FormData(form)
        });

        if(result){

            showMessage(result.message);
        }

        return result;
    }

    function emptyBasket(){

        const basketContent = document.getElementById('basket-content');

        if(basketContent){

            basketContent.innerHTML = '<p>Корзина пустая</p><a href="/Cafe/app/Views/users/menu.php">Перейти в меню</a>';
        }
    }

    document.addEventListener('submit', async function(event){

        const form = event.target;

        if(!(form instanceof HTMLFormElement)){

            return;
        }

        try{

            if(form.classList.contains('ajax-basket-add')){

                event.preventDefault();
                await sendForm(form);
            }

            if(form.classList.contains('ajax-basket-update')){

                event.preventDefault();
                await sendForm(form);
            }

            if(form.classList.contains('ajax-basket-remove')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    form.closest('.basket-item')?.remove();

                    if(result.data.basketCount===0){

                        emptyBasket();
                    }
                }
            }

            if(form.classList.contains('ajax-basket-clear')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    emptyBasket();
                }
            }

            if(form.classList.contains('ajax-create-order')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    const basketContent = document.getElementById('basket-content');

                    if(basketContent){

                        basketContent.innerHTML = '<p>Заказ №' + Number(result.data.orderId) + ' успешно создан</p><a href="/Cafe/app/Views/users/menu.php">Перейти в меню</a>';
                    }
                }
            }

            if(form.classList.contains('ajax-cancel-order-item')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    const item = form.closest('div');

                    form.remove();

                    if(item){

                        item.insertAdjacentHTML('beforeend', '<p>Статус:Отменено</p>');
                    }
                }
            }

            if(form.classList.contains('ajax-order-status')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    await refreshKitchen();
                }
            }

            if(form.classList.contains('ajax-stop-list')){

                event.preventDefault();

                const result = await sendForm(form);

                if(result){

                    const value = Number(result.data.isStopped);
                    const input = form.querySelector('input[name="isStopped"]');
                    const button = form.querySelector('button');

                    input.value = value===1 ? 0 : 1;
                    button.textContent = value===1 ? 'Вернуть в меню' : 'Добавить в стоп-лист';
                }
            }

        }catch(error){

            event.preventDefault();
            showMessage(error.message, true);
        }
    });

    document.addEventListener('change', function(event){

        if(event.target.matches('.ajax-basket-update input[name="quantity"]')){

            event.target.form.requestSubmit();
        }
    });

    const dishSearch = document.getElementById('dish-search');

    if(dishSearch){

        let searchTimer;

        dishSearch.addEventListener('input', function(){

            clearTimeout(searchTimer);

            searchTimer = setTimeout(async function(){

                const search = dishSearch.value.trim();
                const cards = document.querySelectorAll('.dish-card');

                if(search.length<2){

                    cards.forEach(card=>card.hidden = false);
                    document.querySelectorAll('.dish-category').forEach(category=>category.hidden = false);
                    document.getElementById('dish-search-empty').hidden = true;

                    return;
                }

                try{

                    const result = await request(ajaxUrl + '?action=searchDishes&search=' + encodeURIComponent(search));

                    if(!result){

                        return;
                    }

                    const dishIds = result.data.dishIds.map(Number);

                    cards.forEach(function(card){

                        card.hidden = !dishIds.includes(Number(card.dataset.dishId));
                    });

                    document.querySelectorAll('.dish-category').forEach(function(category){

                        category.hidden = !category.querySelector('.dish-card:not([hidden])');
                    });

                    document.getElementById('dish-search-empty').hidden = dishIds.length!==0;

                }catch(error){

                    showMessage(error.message, true);
                }
            }, 300);
        });
    }

    async function refreshKitchen(){

        const container = document.getElementById('kitchen-orders');

        if(!container){

            return;
        }

        const status = document.getElementById('order-status-filter')?.value || 'all';

        try{

            const result = await request(ajaxUrl + '?action=kitchenOrders&status=' + encodeURIComponent(status));

            if(result){

                container.innerHTML = result.data.html;
            }

        }catch(error){

            showMessage(error.message, true);
        }
    }

    const orderStatusFilter = document.getElementById('order-status-filter');

    if(orderStatusFilter){

        orderStatusFilter.addEventListener('change', refreshKitchen);
        setInterval(refreshKitchen, 5000);
    }

    function getCsrfToken(){

        return document.querySelector('input[name="csrfToken"]')?.value || '';
    }

    function tableCards(tables, values){

        if(tables.length===0){

            return '<p>На выбранное время подходящих столов нет</p>';
        }

        return tables.map(function(table){

            return '<div>' +
                '<h3>Стол №' + Number(table.number) + '</h3>' +
                '<p>Количество мест:' + Number(table.seats) + '</p>' +
                '<form action="/Cafe/app/Controlers/ReservationController.php?action=create" method="post">' +
                    '<input type="hidden" name="csrfToken" value="' + escapeHtml(getCsrfToken()) + '">' +
                    '<input type="hidden" name="tableId" value="' + Number(table.id) + '">' +
                    '<input type="hidden" name="date" value="' + escapeHtml(values.date) + '">' +
                    '<input type="hidden" name="time" value="' + escapeHtml(values.time) + '">' +
                    '<input type="hidden" name="durationMinutes" value="' + Number(values.durationMinutes) + '">' +
                    '<input type="hidden" name="guests" value="' + Number(values.guests) + '">' +
                    '<button type="submit">Забронировать</button>' +
                '</form>' +
            '</div><hr>';
        }).join('');
    }

    async function loadAvailableTables(form){

        const values = Object.fromEntries(new FormData(form).entries());
        const parameters = new URLSearchParams(values);

        parameters.set('action', 'availableTables');

        const result = await request(ajaxUrl + '?' + parameters.toString());

        return {result:result, values:values};
    }

    const availableTablesFilter = document.getElementById('available-tables-filter');

    if(availableTablesFilter){

        availableTablesFilter.addEventListener('submit', async function(event){

            event.preventDefault();

            try{

                const response = await loadAvailableTables(availableTablesFilter);

                if(response.result){

                    document.getElementById('available-tables').innerHTML = tableCards(response.result.data.tables, response.values);
                }

            }catch(error){

                showMessage(error.message, true);
            }
        });
    }

    const managerTablesFilter = document.getElementById('manager-tables-filter');

    if(managerTablesFilter){

        managerTablesFilter.addEventListener('submit', async function(event){

            event.preventDefault();

            try{

                const response = await loadAvailableTables(managerTablesFilter);

                if(!response.result){

                    return;
                }

                const tables = response.result.data.tables;
                const tableSelect = document.querySelector('[data-available-table-select]');

                if(tableSelect){

                    tableSelect.innerHTML = '<option value="">Выберите стол</option>' + tables.map(function(table){

                        return '<option value="' + Number(table.id) + '">Стол №' + Number(table.number) + ' — ' + Number(table.seats) + ' мест</option>';
                    }).join('');
                }

                Object.entries(response.values).forEach(function(entry){

                    const input = document.querySelector('[data-reservation-value="' + entry[0] + '"]');

                    if(input){

                        input.value = entry[1];
                    }
                });

                const empty = document.getElementById('manager-tables-empty');
                const button = document.querySelector('[data-reservation-submit]');

                if(empty){

                    empty.hidden = tables.length!==0;
                }

                if(button){

                    button.disabled = tables.length===0;
                }

            }catch(error){

                showMessage(error.message, true);
            }
        });
    }

    const statisticsFilter = document.getElementById('statistics-filter');

    async function loadStatistics(){

        if(!statisticsFilter){

            return;
        }

        const parameters = new URLSearchParams(new FormData(statisticsFilter));

        parameters.set('action', 'statistics');

        try{

            const result = await request(ajaxUrl + '?' + parameters.toString());

            if(!result){

                return;
            }

            const statistics = result.data.statistics;

            document.getElementById('statistics-result').innerHTML =
                '<p>Всего заказов: ' + Number(statistics.totalOrders) + '</p>' +
                '<p>Завершено заказов: ' + Number(statistics.completedOrders) + '</p>' +
                '<p>Отменено заказов: ' + Number(statistics.cancelledOrders) + '</p>' +
                '<p>Заказано блюд: ' + Number(statistics.dishesCount) + '</p>' +
                '<p>Создано бронирований: ' + Number(statistics.reservationsCount) + '</p>' +
                '<p>Зарегистрировано клиентов: ' + Number(statistics.newClients) + '</p>';

        }catch(error){

            showMessage(error.message, true);
        }
    }

    if(statisticsFilter){

        statisticsFilter.addEventListener('submit', function(event){

            event.preventDefault();
            loadStatistics();
        });

        loadStatistics();
    }
});
