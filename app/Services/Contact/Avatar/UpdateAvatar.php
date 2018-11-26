<?php

namespace App\Services\Contact\Avatar;

use App\Services\BaseService;
use App\Models\Contact\Contact;
use App\Models\Account\Photo;
use Illuminate\Validation\Rule;

/**
 * Update the avatar of the contact.
 */
class UpdateAvatar extends BaseService
{
    /**
     * Get the validation rules that apply to the service.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id' => 'required|integer|exists:accounts,id',
            'contact_id' => 'required|integer',
            'source' => [
                'required',
                Rule::in([
                    'adorable',
                    'gravatar',
                    'photo',
                ]),
            ],
            'photo_id' => 'nullable|integer',
        ];
    }

    /**
     * Update message in a conversation.
     *
     * @param array $data
     * @return Contact
     */
    public function execute(array $data) : Contact
    {

        $this->validate($data);

        $contact = Contact::where('account_id', $data['account_id'])
            ->findOrFail($data['contact_id']);

        if (isset($data['photo_id'])) {
            Photo::where('account_id', $data['account_id'])
                ->findOrFail($data['photo_id']);
        }

        $contact->avatar_source = $data['source'];

        // in case of a photo, set the photo as the avatar
        if ($data['source'] == 'photo') {
            $contact->avatar_photo_id = $data['photo_id'];
            $contact->photos()->syncWithoutDetaching([$data['photo_id']]);
        }

        $contact->save();

        return $contact;
    }
}
