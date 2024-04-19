<div class="event-criteria-card" data-counter="{{$counter}}">
    <div class="form-group">
        <label for="event-select">USERS WHO DID THESE EVENTS:</label>
        <select id="event-select" name="did_event_select[]" class="form-control">
            <option value="purchase">Purchase</option>
            <option value="add_to_cart">Add to Cart</option>
            <option value="product_visit">Product Visit</option>
        </select>
    </div>

    <div class="form-group">
        <label for="occurrence-select">Number of Occurrences</label>
        <select id="occurrence-select" name="occurrence-select[]" class="form-control">
            <option value="at_least_once">At Least Once</option>
            <option value="only_once">Only Once</option>
        </select>
    </div>
    
    <!-- Most Recent Event Time -->
    <div class="form-group">
        <label for="time-select">Most Recent Event Time</label>
        <select id="time-select" class="form-control" name="time-select[]">
            <option value="yesterday">Yesterday</option>
            <option value="today">Today</option>
            <option value="within_last_days">Within Last ____ Days</option>
            <option value="before_days">Before ____ Days</option>
        </select>
    </div>

    <!-- Input for 'Within Last ____ Days' initially hidden -->
    <div class="form-group within-last-days-container" style="display: none;">
        <input type="number" id="within-last-days" name="within-last-days[]" class="form-control" placeholder="Enter number of days">
    </div>

    <!-- Input for 'Before ____ Days' initially hidden -->
    <div class="form-group before-days-container" style="display: none;">
        <input type="number" id="before-days" name="before-days[]" class="form-control" placeholder="Enter number of days">
    </div>
        
    <div class="logic-buttons">
        <button type="button" class="btn btn-logic btn-and active-and-or-button" data-value="and">AND</button>&nbsp;&nbsp;
        <button type="button" class="btn btn-logic btn-or" data-value="or">OR</button>
        <input type="hidden" class="and_or_val" name="and_or_val[]" value="and">
    </div>

    <button type="button" class="btn add-event mt-4 addRule">Add Rule +</button>
    <a href="#" class="btn btn-danger deleteRule" style="float: right;background-color:red;color:white">DELETE</a>
    <hr>
</div>