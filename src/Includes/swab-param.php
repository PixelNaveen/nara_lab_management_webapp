
<div class="swab-container">
  <!-- Filter + New -->
  <div class="swab-card-filter">
    <input type="text" placeholder="Search by Parameter Name">
    <select>
      <option>All Status</option>
      <option>Active</option>
      <option>Inactive</option>
    </select>
    <button class="btn-swab-filter">Filter</button>
    <div class="ms-auto">
      <button class="btn-swab-new">+ New Parameter</button>
    </div>
  </div>

  <!-- Table -->
  <div class="swab-table-container">
    <table class="swab-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Code</th>
          <th>Price</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="swab-name">Swab Test A</td>
          <td class="swab-code">STA</td>
          <td class="swab-price">$50</td>
          <td><span class="badge-swab bg-success">Active</span></td>
          <td>
            <button class="btn-swab-edit"><i class="fas fa-edit"></i></button>
            <button class="btn-swab-delete"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modals remain the same as your previous version -->

<script>
  // Elements
  const swabModal = document.getElementById('swabModal');
  const btnNewSwab = document.querySelector('.btn-swab-new');
  const btnCloseSwab = swabModal.querySelector('.btn-close-swab');
  const btnCancelSwab = swabModal.querySelector('.btn-secondary');
  const modalTitle = document.getElementById('swabModalTitle');
  const form = swabModal.querySelector('form');
  const inputName = document.getElementById('swabName');
  const inputPrice = document.getElementById('swabPrice');
  const selectStatus = document.getElementById('swabStatus');

  let editingRow = null;

  btnNewSwab.addEventListener('click', () => {
    modalTitle.textContent = 'New Swab Parameter';
    inputName.value = '';
    inputPrice.value = '';
    selectStatus.value = 'active';
    editingRow = null;
    swabModal.classList.add('active');
  });

  btnCloseSwab.addEventListener('click', () => swabModal.classList.remove('active'));
  btnCancelSwab.addEventListener('click', () => swabModal.classList.remove('active'));
  swabModal.addEventListener('click', (e) => { if(e.target===swabModal) swabModal.classList.remove('active'); });

  // Delete modal
  const deleteModal = document.getElementById('deleteSwabModal');
  const btnCancelDelete = document.getElementById('cancelSwabDelete');
  const btnConfirmDelete = document.getElementById('confirmSwabDelete');
  const closeDeleteBtn = deleteModal.querySelector('.btn-close-swab');
  let rowToDelete = null;

  document.querySelectorAll('.btn-swab-delete').forEach(btn => {
    btn.addEventListener('click', () => { rowToDelete = btn.closest('tr'); deleteModal.classList.add('active'); });
  });

  btnCancelDelete.addEventListener('click', () => { rowToDelete=null; deleteModal.classList.remove('active'); });
  closeDeleteBtn.addEventListener('click', () => { rowToDelete=null; deleteModal.classList.remove('active'); });
  deleteModal.addEventListener('click', (e)=> { if(e.target===deleteModal){ rowToDelete=null; deleteModal.classList.remove('active'); } });

  btnConfirmDelete.addEventListener('click', ()=>{ if(rowToDelete) rowToDelete.remove(); deleteModal.classList.remove('active'); });

  // Edit & Add
  function attachRowListeners(row){
    row.querySelector('.btn-swab-delete').addEventListener('click', () => { rowToDelete=row; deleteModal.classList.add('active'); });
    row.querySelector('.btn-swab-edit').addEventListener('click', () => {
      editingRow = row;
      inputName.value = row.querySelector('.swab-name').textContent;
      inputPrice.value = row.querySelector('.swab-price').textContent.replace('$','');
      selectStatus.value = row.querySelector('span').textContent==='Active' ? 'active':'inactive';
      modalTitle.textContent='Edit Swab Parameter';
      swabModal.classList.add('active');
    });
  }

  document.querySelectorAll('.swab-table tbody tr').forEach(attachRowListeners);

  form.addEventListener('submit', e=>{
    e.preventDefault();
    const name=inputName.value.trim();
    const price=inputPrice.value.trim();
    const status=selectStatus.value;
    if(!name || !price) return;

    if(editingRow){
      editingRow.querySelector('.swab-name').textContent=name;
      editingRow.querySelector('.swab-price').textContent=`$${price}`;
      editingRow.querySelector('td:nth-child(4)').innerHTML=status==='active'
        ? '<span class="badge-swab bg-success">Active</span>'
        : '<span class="badge-swab bg-secondary">Inactive</span>';
    } else {
      const tableBody=document.querySelector('.swab-table tbody');
      const newRow=document.createElement('tr');
      newRow.innerHTML=`
        <td class="swab-name">${name}</td>
        <td class="swab-code">--</td>
        <td class="swab-price">$${price}</td>
        <td>${status==='active'? '<span class="badge-swab bg-success">Active</span>':'<span class="badge-swab bg-secondary">Inactive</span>'}</td>
        <td>
          <button class="btn-swab-edit"><i class="fas fa-edit"></i></button>
          <button class="btn-swab-delete"><i class="fas fa-trash"></i></button>
        </td>`;
      tableBody.appendChild(newRow);
      attachRowListeners(newRow);
    }
    swabModal.classList.remove('active');
  });
</script>