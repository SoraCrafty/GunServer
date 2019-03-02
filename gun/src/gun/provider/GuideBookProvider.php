<?php

namespace gun\provider;

use pocketmine\item\WrittenBook;
use pocketmine\item\WritableBook;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class GuideBookProvider extends Provider
{

    const PROVIDER_ID = "guidebook";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "guidebook";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
                            "title" => "ガイドブック",
                            "author" => "BattleFront2",
                            "generation" => 0,
                            "pages" => []
    					];

    public function open()
    {
        parent::open();
    }

    public function setGuideBook(WrittenBook $book)
    {
        $this->data["title"] = $book->getTitle();
        $this->data["author"] = $book->getAuthor();
        $this->data["generation"] = $book->getGeneration();
        $this->data["pages"] = $this->TagsConvertToArray($book->getPages());
    }

    public function getGuideBook()
    {
        $book = new WrittenBook();
        $book->setTitle($this->data["title"]);
        $book->setAuthor($this->data["author"]);
        $book->setGeneration($this->data["generation"]);
        $book->setPages($this->arrayConvertToTags($this->data["pages"]));
        return $book;
    }

    public function getWritableGuideBook()
    {
        $book = new WritableBook();
        $book->setPages($this->arrayConvertToTags($this->data["pages"]));
        return $book;
    }

    private function TagsConvertToArray($pages)
    {
        $data = [];
        foreach ($pages as $key => $value) {
            $data[] = $value->getString(WritableBook::TAG_PAGE_TEXT, "");
        }
        return $data;
    }

    private function arrayConvertToTags($array)
    {
        $tags = [];
        foreach ($array as $key => $value) {
            $tags[] = new CompoundTag("", [
                new StringTag(WritableBook::TAG_PAGE_TEXT, $value),
                new StringTag(WritableBook::TAG_PAGE_PHOTONAME, "")
            ]);
        }
        return $tags;
    }

}
























