
export default class Ridintek {
  tbody = null;

  constructor(table) {
    this.tbody = $(table).find('tbody');
  }

  addItem(item) {
    console.log(item);
  }
}

export class StockAdjustment {
  tbody = null;

  constructor(table) {
    this.tbody = $(table);
  }

  addItem(item, allowDuplicate = false) {

    if (!allowDuplicate) {
      let items = this.tbody.find('.item_name');
    
      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    this.tbody.append(`
      <tr>
        <input type="hidden" name="item[code][]" class="item_name" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td><input type="number" name="item[quantity][]" class="form-control" min="0"></td>
        <td>${item.quantity}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-trash"></i></a></td>
      </tr>
    `);
  }
}