#Importing Data Into Gaelstream

These are the import scripts used to populate the database:
import_metadata.php
import_biographies.py
import_transcriptions.py
(import_informants.php)
(import_places)

The Python scripts are needed as they use python-docx and pymysql to convert from Word files and populate the DB.

Each of the importers has a "usage" line near the top of the file to demonstrate how it is to be implemented. 

All the import scripts require correct database credentials.

##Steps

1. Run import_metadata.php to populate main recordings data. Important note, the PHP memory allocation may need to be increased. Use: php -d memory_limit=1024M import_metadata.php --file="metadata.xlsx" ...
2. Run import_biographies.py. Note there are 2 source folders, one for informants and one for composers.
3. Run import_transcriptions.py
4. (import_informants.php) and (import_places.php) should only be needed for a dynamic map, the development of which is currently on hold.
