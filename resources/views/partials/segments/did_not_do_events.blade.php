<div class="event-criteria-card mt-3" data-counter="{{$counter}}">
    <div class="form-group">
        <label for="event-select">USERS WHO DID NOT DO THESE EVENTS:</label>
        <select id="event-select" name="did_event_select[]" class="form-control">
            <option value="">Select an option</option>
            <option @isset($rule) @if($rule['did_event_select'] == 'site-visit') selected @endif @endisset value="site-visit">Site Visit</option>
            <option @isset($rule) @if($rule['did_event_select'] == 'visit') selected @endif @endisset value="visit">Product Visit</option>
            <option @isset($rule) @if($rule['did_event_select'] == 'cart') selected @endif @endisset value="cart">Add to Cart</option>
            <option @isset($rule) @if($rule['did_event_select'] == 'purchase') selected @endif @endisset value="purchase">Purchase</option>      
        </select>
    </div>

    <div class="form-group">
        <label for="occurrence-select">Number of Occurrences</label>
        <select id="occurrence-select" name="not-occurrence-select[]" class="form-control">
            <option value="">Select an option</option>
            <option @isset($rule) @if($rule['occurrence_select'] == 'at_least_once') selected @endif @endisset value="at_least_once">At Least Once</option>
            <option @isset($rule) @if($rule['occurrence_select'] == 'only_once') selected @endif @endisset value="only_once">Only Once</option>
        </select>
    </div>
    
    <!-- Most Recent Event Time -->
    <div class="form-group">
        <label for="time-select">Most Recent Event Time</label>
        <select id="did-not-time-select" class="form-control time-select" name="not-time-select[]">
            <option value="">Select an option</option>
            <option @isset($rule) @if($rule['time_select'] == 'yesterday') selected @endif @endisset value="yesterday">Yesterday</option>
            <option @isset($rule) @if($rule['time_select'] == 'today') selected @endif @endisset value="today">Today</option>
            <option @isset($rule) @if($rule['time_select'] == 'within_last_days') selected @endif @endisset value="within_last_days">Within Last ____ Days</option>
            <option @isset($rule) @if($rule['time_select'] == 'before_days') selected @endif @endisset value="before_days">Before ____ Days</option>
        </select>
    </div>

    <!-- Input for 'Within Last ____ Days' initially hidden -->
    <div class="form-group within-last-days-container" @if(isset($rule) && $rule['time_select'] == 'within_last_days') @else style="display: none;" @endif >
        <input type="number" id="within-last-days" name="not-within-last-days[]" @if(isset($rule) && $rule['time_select']) value="{{$rule['within_last_days']}}" @endif class="form-control" placeholder="Enter number of days">
    </div>

    <!-- Input for 'Before ____ Days' initially hidden -->
    <div class="form-group before-days-container" @if(isset($rule) && $rule['time_select'] == 'before_days') @else style="display: none;" @endif>
        <input type="number" id="before-days" name="not-before-days[]" @if(isset($rule) && $rule['time_select'] == 'before_days') value="{{$rule['before_days']}}" @endif class="form-control" placeholder="Enter number of days">
    </div>
        
    <div class="logic-buttons">
        <button type="button" class="btn btn-logic btn-and active-and-or-button" data-value="and">AND</button>&nbsp;&nbsp;
        <button type="button" class="btn btn-logic btn-or" data-value="or">OR</button>
        <input type="hidden" class="and_or_val" name="not_and_or_val[]" @if(isset($rule) && $rule['and_or_val']) value="{{$rule['and_or_val']}}" @else value="and" @endif>
        <button type="button" class="btn add-event deleteNotRule" style="float:right;background-color:red;color:white;font-size:1.2rem">X</button>
        <button type="button" class="btn add-event addNotRule" style="float:right;font-size:1.2rem">+</button>    
    </div>
</div>