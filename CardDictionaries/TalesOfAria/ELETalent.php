<?php

  function ELETalentCardSubType($cardID)
  {
    switch($cardID)
    {
      case "ELE000": return "Landmark";
      case "ELE109": return "Aura";
      case "ELE110": return "Aura";
      case "ELE111": return "Aura";
      case "ELE115": return "Head";
      case "ELE116": return "Head";
      case "ELE117": return "Aura";
      case "ELE143": return "Item";
      case "ELE144": return "Chest";
      case "ELE145": return "Chest";
      case "ELE146": return "Aura";
      case "ELE172": return "Item";
      case "ELE173": return "Arms";
      case "ELE174": return "Arms";
      case "ELE175": return "Aura";
      case "ELE201": return "Item";
      case "ELE233": return "Head";
      case "ELE234": return "Chest";
      case "ELE235": return "Arms";
      case "ELE236": return "Legs";
      default: return "";
    }
  }

  function ELETalentPlayAbility($cardID, $from, $resourcesPaid, $target="-", $additionalCosts="")
  {
    global $currentPlayer, $CS_PlayIndex, $mainPlayer, $actionPoints, $combatChainState, $CCS_GoesWhereAfterLinkResolves, $CS_DamagePrevention, $combatChain, $layers;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID)
    {
      case "ELE000":
        $rv = "Korshem is a partially manual card. Use the instant ability to destroy it when appropriate. Use the Revert Gamestate button under the Stats page if necessary.";
        if($from == "PLAY")
        {
          DestroyLandmark(GetClassState($currentPlayer, $CS_PlayIndex));
          $rv = "Korshem was destroyed";
        }
        return $rv;
      case "ELE103": case "ELE104": case "ELE105":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives the next attack you Fuse this turn +" . EffectAttackModifier($cardID) . ".";
      case "ELE106": GainHealth(3, $currentPlayer); return "Rejuvenate gains 3 health.";
      case "ELE107": GainHealth(2, $currentPlayer); return "Rejuvenate gains 2 health.";
      case "ELE108": GainHealth(1, $currentPlayer); return "Rejuvenate gains 1 health.";
      case "ELE112":
        if (count($combatChain) > 0) {
          AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        } elseif (count($layers) > 0) {
          if (CardType($layers[0] == "AA") || GetAbilityType($layers[0] == "AA")) AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
          else AddCurrentTurnEffect($cardID, $currentPlayer);
        } else {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "Gives your next Lightning, Ice, or Elemental attack this turn +4.";
      case "ELE113":
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("SHOWSELECTEDCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("SHOWSELECTEDCARD", $currentPlayer, "-", 1);
        return "";
      case "ELE114":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your Ice, Earth, and Elemental action cards +1 defense this turn.";
      case "ELE115":
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDCLASSSTATE", $currentPlayer, $CS_DamagePrevention . "-1", 1);
        return "";
      case "ELE116":
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("CHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("SHOWSELECTEDCARD", $currentPlayer, "-", 1);
        return "";
      case "ELE118":
        MyDrawCard();
        MyDrawCard();
        MyDrawCard();
        return "Draws 3 cards.";
      case "ELE119": case "ELE120": case "ELE121":
        if($from == "ARS")
        {
          $rv = "Goes to the bottom of your deck when the combat chain closes.";
        }
        return $rv;
      case "ELE122": case "ELE123": case "ELE124":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next Earth or Elemental attack action card this turn +" . EffectAttackModifier($cardID) .", and +1 if it's fused.";
      case "ELE125": case "ELE126": case "ELE127":
       AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
       AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
       AddDecisionQueue("COMBATCHAINBUFFDEFENSE", $currentPlayer, PlayBlockModifier($cardID), 1);
       return "Gives target defending card " . PlayBlockModifier($cardID) . ".";
      case "ELE131": case "ELE132": case "ELE133":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENAL");
        AddDecisionQueue("MAYCHOOSEARSENAL", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "ELE137": case "ELE138": case "ELE139":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next attack action card this turn +" . EffectAttackModifier($cardID) .".";
      case "ELE140": case "ELE141": case "ELE142":
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("CHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("SHOWSELECTEDCARD", $currentPlayer, "-", 1);
        if($from == "ARS") AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "ELE143":
        if($from == "PLAY")
        {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gives your attack actions cards +1 power and +1 defense for the rest of the turn.";
        }
        return $rv;
      case "ELE144":
        AddCurrentTurnEffect($cardID, $otherPlayer);
        return "Makes cards and activated abilities of your opponent cost +1 resource this turn.";
      case "ELE145":
        PlayAura("ELE111", $otherPlayer);
        return "Creates a frostbite for the other player.";
      case "ELE147":
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose_to_pay_2_or_you_lose_and_can't_gain_go_again.");
        AddDecisionQueue("BUTTONINPUT", $mainPlayer, "0,2", 0, 1);
        AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
        AddDecisionQueue("BLIZZARDLOG", $mainPlayer, "-", 1);
        AddDecisionQueue("GREATERTHANPASS", $mainPlayer, "0", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, $cardID, 1);
        return "";
      case "ELE151": case "ELE152": case "ELE153":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
        return "Gives your next attack this turn +" . EffectAttackModifier($cardID) . " and each hit creates a frostbite.";
      case "ELE154": case "ELE155": case "ELE156":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next Ice or Elemental attack action card this turn +" . EffectAttackModifier($cardID) . " and Dominate if it's fused.";
      case "ELE163": case "ELE164": case "ELE165":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Makes your next Ice or Elemental attack create Frostbites for your opponent.";
      case "ELE166": case "ELE167": case "ELE168":
        if($cardID == "ELE166") $cost = 3;
        else if($cardID == "ELE167") $cost = 2;
        else $cost = 1;
        AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose_if_you_want_to_pay_".$cost."_to_prevent_your_opponent_next_attack_to_gain_Dominate.");
        AddDecisionQueue("BUTTONINPUT", $otherPlayer, "0," . $cost, 0, 1);
        AddDecisionQueue("PAYRESOURCES", $otherPlayer, "<-", 1);
        AddDecisionQueue("GREATERTHANPASS", $otherPlayer, "0", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
        if($from == "ARS") MyDrawCard();
        return "";
      case "ELE169": PayOrDiscard($otherPlayer, 3); return "Makes the opponent pay 3 or discard a card.";
      case "ELE170": PayOrDiscard($otherPlayer, 2); return "Makes the opponent pay 2 or discard a card.";
      case "ELE171": PayOrDiscard($otherPlayer, 1); return "Makes the opponent pay 1 or discard a card.";
      case "ELE172":
        if($from == "PLAY")
        {
          PayOrDiscard($otherPlayer, 2);
          $rv = "Makes your opponent pay 2 resources or discard a card.";
        }
        return $rv;
      //Lightning
      case "ELE173":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Makes your next attack action card deal 1 damage if it hits.";
      case "ELE176":
        if($currentPlayer == $mainPlayer) {++$actionPoints; $rv = "Grants an action point."; }
        return $rv;
      case "ELE177": case "ELE178": case "ELE179":
        AddAfterResolveEffect($cardID, $currentPlayer);
        return "Gives your next applicable action card go again.";
      case "ELE180": case "ELE181": case "ELE182":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next lightning or elemental attack +" . EffectAttackModifier($cardID) . " and go again if it's fused.";
      case "ELE183": case "ELE184": case "ELE185":
        $amount = 3;
        if($cardID == "ELE184") $amount = 2;
        else if($cardID == "ELE185") $amount = 1;
        if (count($combatChain) != 0) {
          CombatChainPowerModifier(intval(explode("-", $target)[1]), $amount);
        }
        else {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "ELE186": case "ELE187": case "ELE188":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "ELE189": case "ELE190": case "ELE191":
        if($from == "ARS") { $rv = "Gains go again."; GiveAttackGoAgain(); }
        return $rv;
      case "ELE195": case "ELE196": case "ELE197":
        if($from == "PLAY")
        {
          AddCurrentTurnEffect($cardID, $currentPlayer, "", 1);
          $rv = "Deals 1 extra damage if hits a hero.";
        }
        return $rv;
      case "ELE198": case "ELE199": case "ELE200":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        if($from == "ARS") MyDrawCard();
        return $rv;
      case "ELE201":
        if($from == "PLAY")
        {
          if (count($combatChain) > 0) GiveAttackGoAgain();
          else AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gives target action go again.";
        }
        return $rv;
      case "ELE233":
        MyDrawCard();
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("CHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("OPT", $currentPlayer, "<-");
        return "";
      case "ELE234":
        GainResources($currentPlayer, 3);
        return "Gain 3 resources.";
      case "ELE235":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next attack action card this turn +1.";
      case "ELE236":
        IncrementClassState($currentPlayer, $CS_DamagePrevention);
        return "Prevents the next 1 damage that would be dealt to you this turn.";
      default: return "";
    }
  }

  function ELETalentHitEffect($cardID)
  {
    global $mainPlayer, $defPlayer;
    switch($cardID)
    {
      case "ELE148": case "ELE149": case "ELE150":
        if(IsHeroAttackTarget()) {
          PayOrDiscard($defPlayer, 2);
        }
        break;
      case "ELE157": case "ELE158": case "ELE159":
        if(IsHeroAttackTarget())
        {
          PlayAura("ELE111", $defPlayer);
        }
        break;
      default: break;
    }
  }

  function SowTomorrowIndices($player, $cardID)
  {
    if($cardID == "ELE140") $minCost = 0;
    else if($cardID == "ELE141") $minCost = 1;
    else $minCost = 2;
    $earth = CombineSearches(SearchDiscard($player, "A", "", -1, $minCost, "", "EARTH"), SearchDiscard($player, "AA", "", -1, $minCost, "", "EARTH"));
    $elemental = CombineSearches(SearchDiscard($player, "A", "", -1, $minCost, "", "ELEMENTAL"), SearchDiscard($player, "AA", "", -1, $minCost, "", "ELEMENTAL"));
    return CombineSearches($earth, $elemental);
  }

  function SummerwoodShelterIndices($player)
  {
    global $combatChain;
    $indices = "";
    for($i=0; $i<count($combatChain); $i += CombatChainPieces())
    {
      if($combatChain[$i+1] == $player)
      {
        $cardType = CardType($combatChain[$i]);
        if($cardType == "A" || $cardType == "AA")
        {
          if(TalentContains($combatChain[$i], "EARTH") || TalentContains($combatChain[$i], "ELEMENTAL"))
          {
            if($indices != "") $indices .= ",";
            $indices .= $i;
          }
        }
      }
    }
    return $indices;
  }

  function PlumeOfEvergrowthIndices($player)
  {
    $indices = CombineSearches(SearchDiscard($player, "A", "", -1, -1, "", "EARTH"), SearchDiscard($player, "AA", "", -1, -1, "", "EARTH"));
    $indices = CombineSearches($indices, SearchDiscard($player, "I", "", -1, -1, "", "EARTH"));
    return $indices;
  }

  function PulseOfCandleholdIndices($player)
  {
    return CombineSearches(SearchDiscard($player, "A", "", -1, -1, "", "EARTH,LIGHTNING,ELEMENTAL"), SearchDiscard($player, "AA", "", -1, -1, "", "EARTH,LIGHTNING,ELEMENTAL"));
  }

  function ExposedToTheElementsEarth($player)
  {
      $otherPlayer = $player == 1 ? 2 : 1;
      PrependDecisionQueue("ADDNEGDEFCOUNTER", $otherPlayer, "-", 1);
      PrependDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      PrependDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP");
  }

  function ExposedToTheElementsIce($player)
  {
      $otherPlayer = $player == 1 ? 2 : 1;
      PrependDecisionQueue("DESTROYTHEIRCHARACTER", $player, "-", 1);
      PrependDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      PrependDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP0", 1);
      PrependDecisionQueue("WRITELOG", $player, "Declined_to_pay_for_Exposed_to_the_Elements.", 1);
      PrependDecisionQueue("GREATERTHANPASS", $otherPlayer, "0", 1);
      PrependDecisionQueue("PAYRESOURCES", $otherPlayer, "<-", 1);
      PrependDecisionQueue("BUTTONINPUT", $otherPlayer, "0,2", 0, 1);
      PrependDecisionQueue("SETDQCONTEXT", $otherPlayer, "Pay_2_to_prevent_an_equipment_from_being_destroyed");
      WriteLog("Player " . $otherPlayer . " may choose to pay 2 to prevent their equipment from being destroyed.");
  }

  function KorshemRevealAbility($player)
  {
    WriteLog("Korshem triggered by revealing a card.");
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose a bonus", 1);
    AddDecisionQueue("BUTTONINPUT", $player, "Gain_a_resource,Gain_a_life,1_Attack,1_Defense");
    AddDecisionQueue("KORSHEM", $player, "-", 1);
  }

?>
