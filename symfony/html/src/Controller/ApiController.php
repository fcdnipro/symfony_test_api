<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Repository\BookRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/authors", name="create_author", methods={"POST"})
     */
    public function createAuthor(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = new Author();
        $author->setFirstName($data['firstName']);
        $author->setLastName($data['lastName']);
        $author->setSurname($data['surname']);
        $this->getDoctrine()->getManager()->persist($author);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse(['message' => 'Author created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/authors/{lastName}", name="get_author_by_last_name", methods={"GET"})
     */
    public function getAuthorByLastName($lastName): JsonResponse
    {
        $author = $this->getDoctrine()->getRepository(Author::class)->findOneBy(['last_name' => $lastName]);

        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('serializer')->normalize($author, null, ['groups' => 'api']);

        return new JsonResponse($data, Response::HTTP_OK);
    }


    /**
     * @Route("/authors", name="get_all_authors", methods={"GET"})
     */
    public function getAllAuthors(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $query = $this->getDoctrine()->getRepository(Author::class)->createQueryBuilder('b')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );
        $data = $this->get('serializer')->normalize($pagination->getItems(), null, ['groups' => 'api']);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="get_one_book", methods={"GET"})
     */
    public function getOneBook($id): JsonResponse
    {
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('serializer')->normalize($book, null, ['groups' => 'api']);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="edit_book", methods={"POST"})
     */
    public function editBook($id, Request $request, SluggerInterface $slugger): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->request->all();

        $book->setTitle($data['title']);
        $book->setDescription($data['description']);
        $book->setPublicationDate(new \DateTime($data['publicationDate']));

        foreach ($data['authors'] as $authorData) {
            $lastName = $authorData['lastName'];

            $author = $this->getDoctrine()->getRepository(Author::class)->findOneBy(['last_name' => $lastName]);

            if (!$author) {
                // Only create a new author if it doesn't already exist
                $author = new Author();
                $author->setFirstName($authorData['firstName']);
                $author->setLastName($lastName);
                $author->setSurname($authorData['surname']);

                $entityManager->persist($author);
            }

            $book->addAuthor($author);
        }

        $uploadedFile = $request->files->get('imageFile');
        if ($uploadedFile instanceof UploadedFile) {
            $originalFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $uploadedFile->getClientOriginalName());

            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                $uploadedFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $book->setImageFilename($newFilename);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Error uploading the image'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['error' => 'Image file is required'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Book edited successfully'], Response::HTTP_OK);
    }


    /**
     * @Route("/books", name="get_all_books", methods={"GET"})
     */
    public function getAllBooks(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $query = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('b')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );
        $data = $this->get('serializer')->normalize($pagination->getItems(), null, ['groups' => 'api']);

        return new JsonResponse($data, Response::HTTP_OK);
    }


    /**
     * @Route("/books", name="create_book", methods={"POST"}, )
     * @throws \Exception
     */
    public function createBook(Request $request, SluggerInterface $slugger): JsonResponse
    {

        $data = $request->request->all();

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);
        $book->setPublicationDate(new \DateTime($data['publicationDate']));

        foreach ($data['authors'] as $authorData) {
            $lastName = $authorData['lastName'];

            $author = $this->getDoctrine()->getRepository(Author::class)->findOneBy(['last_name' => $lastName]);

            if (!$author) {
                $author = new Author();
                $author->setFirstName($authorData['firstName']);
                $author->setLastName($lastName);
                $author->setSurname($authorData['surname']);

                $this->getDoctrine()->getManager()->persist($author);
            }

            $book->addAuthor($author);
        }
        $uploadedFile = $request->files->get('imageFile');
        if ($uploadedFile instanceof UploadedFile) {
            $originalFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $uploadedFile->getClientOriginalName());

            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                $uploadedFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $book->setImageFilename($newFilename);
            } catch (FileException $e) {
                var_dump($e->getMessage());die();
                return new JsonResponse(['error' => 'Error uploading the image'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['error' => 'Image file is required'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Book created successfully'], Response::HTTP_CREATED);
    }
}
