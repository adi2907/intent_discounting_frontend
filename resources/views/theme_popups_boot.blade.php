<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<button type="button" class="btn btn-primary" id="popupModal" style="display: none;" data-toggle="modal" data-target="#exampleModal">
  Launch demo modal
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a class="close" href="#">&times;</a>
        <div class="content">
            <form id="newUserForm">
                <div class="form-group">
                <label for="userName">Full Name</label>
                <input id="userName" type="text" name="fullName" class="form-control" required>
                </div>
                <div class="form-group">
                <label for="userPhone">Mobile</label>
                <input id="userPhone" type="number" name="phone" class="form-control">
                </div>
                <button id="submit" type="submit" name="newUserSubmit" class="add-cart-cta">Submit</button>
            </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>