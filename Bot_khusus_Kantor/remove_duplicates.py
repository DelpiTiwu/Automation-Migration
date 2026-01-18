
import json

file_path = 'd:/Bot_khusus_Kantor/hasil_panen.json'

try:
    with open(file_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    print(f"Original item count: {len(data)}")

    unique_data = []
    seen_names = set()

    for item in data:
        name = item.get('nama')
        # If name is None, we might want to keep it or handle it. 
        # Assuming product must have a name to be a duplicate checking key.
        if name:
            if name not in seen_names:
                unique_data.append(item)
                seen_names.add(name)
        else:
            # If it has no name, we keep it? Or treat it as unique?
            # Let's assume entries without names are rare or shouldn't be deduped against each other blindly.
            # But based on previous file view, they seem to have names.
            # safe fallback: treat it as unique if no name, or just add it.
            unique_data.append(item)
    
    print(f"New item count: {len(unique_data)}")
    print(f"Removed {len(data) - len(unique_data)} duplicates.")

    with open(file_path, 'w', encoding='utf-8') as f:
        json.dump(unique_data, f, indent=4)
        
    print("Successfully removed duplicates and saved file.")

except Exception as e:
    print(f"Error: {e}")
