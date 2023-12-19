docker-compose up -d --build в папке с проектом

composer install в контейнере пхп

api 

http://localhost/books (паггинация по 5 книг, следующие книги ?page={page_counter}

http://localhost/api/books/{id} 

http://localhost/api/authors?page=2 аналогично по 5 авторов

http://localhost/api/authors/{lastName}

http://localhost/api/authors POST 
body:{
"firstName": "1",
"lastName": "2",
"surname":"3"
}

edit book
curl -X POST http://localhost/api/books/11 -F  "title={title}" -F "description={description}" -F "publicationDate={publicationDate}" -F "authors[0][firstName]=Random" -F "authors[0][lastName]=Author2" -F "imageFile=@{file_path}"

create book
curl -X POST http://localhost/api/books -H "Content-Type: multipart/form-data" -F "title={title}" -F "description={description}" -F "publicationDate={publicationDate}" -F "authors[0][firstName]=Random" -F "authors[0][lastName]=Author2" -F "imageFile=@{file_path}"
