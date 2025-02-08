from bs4 import BeautifulSoup
import pandas as pd

# Buka file HTML
with open('localhost _ 127.0.0.1 _ magang _ phpMyAdmin 5.2.1.html', 'r', encoding='utf-8') as file:
    soup = BeautifulSoup(file, 'html.parser')

# Cari tabel HTML
tables = soup.find_all('table')

# Konversi setiap tabel menjadi DataFrame
for idx, table in enumerate(tables):
    df = pd.read_html(str(table))[0]  # Membaca tabel pertama

    # Buat perintah SQL untuk membuat tabel
    table_name = f'table_{idx + 1}'  # Nama tabel sementara
    create_table_sql = f'CREATE TABLE {table_name} (\n'
    for col in df.columns:
        create_table_sql += f'  `{col}` VARCHAR(255),\n'

    create_table_sql = create_table_sql.rstrip(',\n') + '\n);'

    # Buat perintah INSERT untuk setiap baris
    insert_statements = []
    for _, row in df.iterrows():
        values = ', '.join([f\"'{str(value)}'\" for value in row])
        insert_statements.append(f'INSERT INTO {table_name} VALUES ({values});')

    # Tampilkan hasil
    print(create_table_sql)
    for stmt in insert_statements:
        print(stmt)

    print('\n---\n')
