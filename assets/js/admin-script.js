document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ahbn-add-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let target = this.dataset.target;
            let wrapper = document.getElementById(target + '_wrapper');
            let newRow = document.createElement('div');
            newRow.className = 'ahbn-repeater-row';

            if(target==='extras'){
                newRow.innerHTML = '<input type="text" name="extras[]" placeholder="Feature Name" required> '+
                                   '<input type="number" name="extras_price[]" placeholder="Price" step="0.01"> '+
                                   '<button type="button" class="button ahbn-remove-btn">Remove</button>';
            } else if(target==='amenities'){
                let iconOptions = '';
                for (const [cls,label] of Object.entries(ahbn_hotel_icons)) {
                    iconOptions += `<option value="${cls}">${label}</option>`;
                }
                newRow.innerHTML = '<input type="text" name="amenities[]" placeholder="Amenity Name" required> '+
                                   `<select name="amenities_icon[]" class="amenity-icon-select">${iconOptions}</select>`+
                                   ' <span class="icon-preview dashicons"></span> '+
                                   '<button type="button" class="button ahbn-remove-btn">Remove</button>';
            } else {
                newRow.innerHTML = `<input type="text" name="${target}[]" placeholder="${target==='room_types'?'Room Type':'Room No'}" required> `+
                                   '<button type="button" class="button ahbn-remove-btn">Remove</button>';
            }
            wrapper.appendChild(newRow);
        });
    });

    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('ahbn-remove-btn')){
            e.target.parentNode.remove();
        }
    });

    document.addEventListener('change', function(e){
        if(e.target && e.target.classList.contains('amenity-icon-select')){
            let iconSpan = e.target.nextElementSibling;
            iconSpan.className = 'icon-preview dashicons ' + e.target.value;
        }
    });
});
