<?php

namespace Akyos\CanopeeSDK\Controller\Faq\components;

use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeSDK\Form\Faq\FaqSearchType;
use Akyos\CanopeeModuleSDK\Class\Get;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('faq.list', template: '@CanopeeSDK/faq/components/FaqListComponent.html.twig')]
final class FaqListComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly TranslatorInterface   $translator,
        private readonly ProviderService       $provider,
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }

    #[LiveProp (writable: true)]
    public $faq = [];

    #[LiveProp (writable: true)]
    public ?string $search = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(FaqSearchType::class);
    }

    #[LiveAction]
    public function getFaq()
    {
        /** @var Customer $customer */
        $customer = $this->getUser()->getCustomer();

        $query = (new Get())
            ->setResource('question_categories')
            ->setQueryParams([
                'module.slug' => $this->parameterBag->get('module_slug'),
                'customer.id' => $customer->getCustomerCanopee()->id,
            ])
        ;

        $this->faq['title'] = $this->translator->trans('app', [], 'common');
        $this->faq['categories'] = $this->provider->initialize('canopee')->send($query)->getData();

        if ($this->search) {

            foreach ($this->faq['categories'] as $category) {

                $category->questions = array_filter($category->questions, function ($question) {
                    return stripos($question->title, $this->search) || stripos($question->answer, $this->search);
                });

            }

            $this->faq['categories'] = array_filter($this->faq['categories'], function ($category) {
                return count($category->questions) > 0;
            });

        }

    }
}
