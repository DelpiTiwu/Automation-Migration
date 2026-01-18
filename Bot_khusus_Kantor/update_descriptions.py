import json
import os

file_path = r'd:\BOTT\hasil_panen_ready.json'

def update_descriptions():
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        return

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        updated_count = 0
        for item in data:
            description = item.get('deskripsi', '')
            # Check if description is None, empty, or just whitespace
            if not description or not description.strip():
                item['deskripsi'] = item.get('nama', '')
                updated_count += 1
                print(f"Updated description for: {item.get('nama')}")
        
        if updated_count > 0:
            with open(file_path, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=4, ensure_ascii=False)
            print(f"Successfully updated {updated_count} items.")
        else:
            print("No items needed updating.")
            
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    update_descriptions()
