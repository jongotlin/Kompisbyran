<?php

namespace LeanlinkBundle\Tests\Service;

use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\MusicCategory;
use AppBundle\Entity\User;
use AppBundle\Enum\FriendTypes;
use AppBundle\Validator\Constraints\UserHasMusicCategories;
use AppBundle\Validator\Constraints\UserHasMusicCategoriesValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

class UserHasMusicCategoriesValidatorTest extends AbstractConstraintValidatorTest
{
    protected function createValidator()
    {
        return new UserHasMusicCategoriesValidator();
    }

    /**
     * @test
     */
    public function nonMusicConnectionRequestIsValid()
    {
        $connectionRequestMock = $this->getMock('AppBundle\Entity\ConnectionRequest');
        $connectionRequestMock->expects($this->once())->method('getType')->willReturn(FriendTypes::FRIEND);
        $connectionRequestMock->expects($this->never())->method('getUser');
        $this->validator->validate($connectionRequestMock, new UserHasMusicCategories());
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function musicConnectionRequestWithMusicCategoriesIsValid()
    {
        $userMock = $this->getMock('AppBundle\Entity\User');
        $userMock->expects($this->once())->method('getMusicCategories')->willReturn([$this->getMock('AppBundle\Entity\MusicCategory')]);
        $connectionRequestMock = $this->getMock('AppBundle\Entity\ConnectionRequest');
        $connectionRequestMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $connectionRequestMock->expects($this->once())->method('getType')->willReturn(FriendTypes::MUSIC);
        $this->validator->validate($connectionRequestMock, new UserHasMusicCategories());
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function musicConnectionRequestWithoutMusicCategoriesIsNotValid()
    {
        $userMock = $this->getMock('AppBundle\Entity\User');
        $userMock->expects($this->once())->method('getMusiCCategories')->willReturn([]);
        $connectionRequestMock = $this->getMock('AppBundle\Entity\ConnectionRequest');
        $connectionRequestMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $connectionRequestMock->expects($this->once())->method('getType')->willReturn(FriendTypes::MUSIC);
        $this->validator->validate($connectionRequestMock, new UserHasMusicCategories());
        $this->buildViolation('För att vara musikompis måste du ha anget några musikkategorier på din profil.')->assertRaised();
    }
}
