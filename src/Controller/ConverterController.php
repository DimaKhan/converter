<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Currencies;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ConverterController extends AbstractController
{
    /**
     * @Route("/converter", name="converter")
     */
    public function index(): Response
    {
        // Получаем данные POST запроса
        $request = Request::createFromGlobals();
        $inputs = $request->toArray();

        $validator = Validation::createValidator();

        // Правила валидации для параметров POST запроса
        $constraints = new Assert\Collection([
            'from' => [new NotBlank(null, '«From» should not be blank')],
            'to' => [new NotBlank(null, '«To» should not be blank')],
            'amount' => [new NotBlank(null, '«Amount» should not be blank')],
        ]);

        // Валидация данных POST запроса
        $violations = $validator->validate($inputs, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            // Возвращаем ответ с найденными ошибками во входных параметрах
            return $this->json([
                'result' => 'failed',
                'errors' => $errors,
            ]);
        }

        $currencies = new Currencies();
        // Конвертируем валюту
        $result = $currencies->convert($inputs['from'], $inputs['to'], $inputs['amount']);

        // Возвращаем результат конвертации
        return $this->json([
            'result' => 'success',
            'amount' => $result,
        ]);
    }
}
