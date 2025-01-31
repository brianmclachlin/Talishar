<?php

function DYNAbilityCost($cardID)
{
  switch ($cardID) {
    case "DYN001": return 3;
    case "DYN005": return 3;
    case "DYN025": return 3;
    case "DYN046": return 0;
    //Warrior
    case "DYN067": return 1;
    case "DYN068": return 3;
    case "DYN069": case "DYN070": return 1;
    case "DYN115": case "DYN116": return 2;
    case "DYN117": return 0;
    case "DYN118": return 0;
    case "DYN151": return 1;
    case "DYN172": return 3;
    case "DYN192": return 2;
    case "DYN193": return 3;
    case "DYN235": return 1;
    case "DYN240": return 0;
    case "DYN241": return 0;
    case "DYN242": return 1;
    case "DYN243": return 2;
    case "DYN492a": return 0;
    case "DYN612": return 2;
    default: return 0;
  }
}

function DYNAbilityType($cardID, $index = -1)
{
  switch ($cardID) {
    case "DYN001": return "A";
    case "DYN005": return "AA";
    case "DYN046": return "I";
    //Warrior
    case "DYN067": return "AA";
    case "DYN068": return "AA";
    case "DYN069": case "DYN070": return "AA";
    case "DYN088": return "AA";
    case "DYN025": return "I";
    case "DYN115": case "DYN116": return "AA";
    case "DYN117": return "AR";
    case "DYN118": return "AR";
    case "DYN151": return "A";
    case "DYN171": return "I";
    case "DYN172": return "A";
    case "DYN192": return "A";
    case "DYN193": return "A";
    case "DYN235": return "I";
    case "DYN240": return "A";
    case "DYN241": return "A";
    case "DYN242": case "DYN243": return "A";
    case "DYN492a": return "AA";
    case "DYN612": return "AA";
    default: return "";
  }
}
// Natural go again or ability go again. Attacks that gain go again should be in CoreLogic (due to hypothermia)
function DYNHasGoAgain($cardID)
{
  switch ($cardID) {
    //Brute
    case "DYN009": return true;
    case "DYN022": case "DYN023": case "DYN024": return true;
    //Guardian
    case "DYN028": return true;
    //Ninja
    case "DYN049": return true;
    case "DYN050": case "DYN051": case "DYN052": return true;
    case "DYN062": case "DYN063": case "DYN064": return true;
    case "DYN065": return true;
    //Warrior
    case "DYN071": return true;
    case "DYN076": case "DYN077": case "DYN078": return true;
		case "DYN082": case "DYN083": case "DYN084": return true;
		case "DYN085": case "DYN086": case "DYN087": return true;
    //Mechanologist
    case "DYN091": return true;
    case "DYN092": return true;
    //Assassin
    case "DYN115": case "DYN116": return true;
    //Ranger
    case "DYN155": return true;
		case "DYN168": case "DYN169": case "DYN170": return true;
    //Runeblade
		case "DYN185": case "DYN186": case "DYN187": return true;
    case "DYN188": case "DYN189": case "DYN190": return true;
    case "DYN209": case "DYN210": case "DYN211": return true;
    //Illusionist
    case "DYN212": return true;
    case "DYN230": case "DYN231": case "DYN232": return true;
    default: return false;
  }
}

function DYNAbilityHasGoAgain($cardID)
{
  switch ($cardID) {
    case "DYN151": return true;
    case "DYN192": return true;
    case "DYN240": return true;
    case "DYN243": return true;
  }
}

function DYNEffectAttackModifier($cardID)
{
  global $mainPlayer;
  $params = explode(",", $cardID);
  $cardID = $params[0];
  if (count($params) > 1) $parameter = $params[1];
  switch ($cardID) {
    case "DYN007": return 6;
    case "DYN013": return 3;
    case "DYN014": return 2;
    case "DYN015": return 1;
    case "DYN019": case "DYN020": case "DYN021": return 3;
    case "DYN022": return 4;
    case "DYN023": return 3;
    case "DYN024": return 2;
    case "DYN028": return 1;
    case "DYN046": return 2;
    case "DYN049": return 1;
    case "DYN053": return 3;
    case "DYN054": return 2;
    case "DYN055": return 1;
    case "DYN071": return 4;
		case "DYN073": return 3;
    case "DYN074": return 2;
    case "DYN075": return 1;
    case "DYN076": return NumEquipBlock() > 0 ? 3 : 0;
    case "DYN077": return NumEquipBlock() > 0 ? 2 : 0;
    case "DYN078": return NumEquipBlock() > 0 ? 1 : 0;
    case "DYN082": return 6;
    case "DYN083": return 5;
    case "DYN084": return 4;
    case "DYN085": return NumEquipBlock() > 0 ? 3 : 0;
    case "DYN086": return NumEquipBlock() > 0 ? 2 : 0;
    case "DYN087": return NumEquipBlock() > 0 ? 1 : 0;
    case "DYN089-UNDER": return 1;
    case "DYN091-1": return 3;
    case "DYN155": return 3;
    case "DYN156": case "DYN157": case "DYN158": return NumEquipBlock() > 0 ? 1 : 0;
    case "DYN165": case "DYN166": case "DYN167": return 2;
    case "DYN168": return 3;
    case "DYN169": return 2;
    case "DYN170": return 1;
    case "DYN176": case "DYN177": case "DYN178": return 2;
		case "DYN185-BUFF": case "DYN186-BUFF": case "DYN187-BUFF": return 1;
    default:
      return 0;
  }
}

function DYNCombatEffectActive($cardID, $attackID)
{
  global $combatChainState, $CCS_IsBoosted, $mainPlayer;
  $params = explode(",", $cardID);
  $cardID = $params[0];
  switch ($cardID) {
    case "DYN007": return true;
    case "DYN013": case "DYN014": case "DYN015": return AttackValue($attackID) >= 6;
    case "DYN019": case "DYN020": case "DYN021": return true;
    case "DYN022": case "DYN023": case "DYN024": return ClassContains($attackID, "BRUTE", $mainPlayer);
    case "DYN028": return ClassContains($attackID, "GUARDIAN", $mainPlayer);
    case "DYN046": return $attackID == "DYN065";
    case "DYN049": return $attackID == "DYN065";
    case "DYN053": case "DYN054": case "DYN055": return $attackID == "DYN065";
    case "DYN068": return true;
    case "DYN071": return CardSubType($attackID) == "Axe";
    case "DYN073": case "DYN074": case "DYN075": return CardType($attackID) == "W";
    case "DYN076": case "DYN077": case "DYN078":
      $subtype = CardSubType($attackID);
      return $subtype == "Sword" || $subtype == "Dagger";
    case "DYN082": case "DYN083": case "DYN084": return CardSubType($attackID) == "Axe";
		case "DYN085": case "DYN086": case "DYN087": return (CardSubType($attackID) == "Sword" || CardSubType($attackID) == "Dagger");
    case "DYN089-UNDER":
      $character = &GetPlayerCharacter($mainPlayer);
      $index = FindCharacterIndex($mainPlayer, "DYN492a");
      return $attackID == "DYN492a" && $character[$index + 2] >= 1;
    case "DYN091-1": return $combatChainState[$CCS_IsBoosted];
    case "DYN115": case "DYN116": return NumAttacksBlocking() > 0;
    case "DYN154": return true;
    case "DYN155": return CardSubType($attackID) == "Arrow";
    case "DYN156": case "DYN157": case "DYN158": return true;
		case "DYN165": case "DYN166": case "DYN167": return true;
		case "DYN168": case "DYN169": case "DYN170": return CardSubType($attackID) == "Arrow";
    case "DYN176": case "DYN177": case "DYN178": return true;
		case "DYN185-BUFF": case "DYN186-BUFF": case "DYN187-BUFF": return ClassContains($attackID, "RUNEBLADE", $mainPlayer);
		case "DYN185-HIT": case "DYN186-HIT": case "DYN187-HIT": return true;
    default:
      return false;
  }
}

function DYNCardTalent($cardID)
{
  $number = intval(substr($cardID, 3));
  if ($number <= 0) return "";
  else if ($number >= 1 && $number <= 2) return "ROYAL,DRACONIC";
  else if ($number >= 3 && $number <= 4) return "DRACONIC";
  else if ($number == 66 || $number == 212 || $number == 612) return "LIGHT";
  else return "NONE";
}

function DYNCardSubtype($cardID)
{
  switch ($cardID) {
    case "DYN002": return "Ash";
    case "DYN003": return "Ash";
    case "DYN004": return "Ash";
    //Brute
    case "DYN005": return "Rock";
    case "DYN006": return "Legs";
    case "DYN013": case "DYN014": case "DYN015": return "Aura";
    case "DYN026": return "Off-Hand";
    //Guardian
    case "DYN027": return "Off-Hand";
    case "DYN029": return "Aura";
    case "DYN033": case "DYN034": case "DYN035": return "Aura";
    //Ninja
    case "DYN045": return "Chest";
    case "DYN046": return "Arms";
    case "DYN048": return "Aura";
    case "DYN053": case "DYN054": case "DYN055": return "Aura";
    //Warrior
    case "DYN066": return "Item";
    case "DYN067": return "Sword";
    case "DYN068": return "Axe";
    case "DYN069": case "DYN070": return "Dagger";
    case "DYN072": return "Aura";
    case "DYN073": case "DYN074": case "DYN075": return "Aura";
    //Mechanologist
    case "DYN088": return "Gun";
    case "DYN089": return "Arms";
    case "DYN092": return "Construct";
    case "DYN093": return "Item";
    case "DYN094": return "Item";
    case "DYN098": case "DYN099": case "DYN100": return "Aura";
    case "DYN110": case "DYN111": case "DYN112": return "Item";
    case "DYN492b": return "Chest";
    case "DYN492c": return "Item";
    //Assassin
    case "DYN115": case "DYN116": return "Dagger";
    case "DYN117": return "Legs";
    case "DYN118": return "Head";
    //Ranger
    case "DYN151": return "Bow";
    case "DYN152": return "Arms";
    case "DYN153": return "Arrow";
    case "DYN154": return "Arrow";
    case "DYN156": case "DYN157": case "DYN158": return "Arrow";
		case "DYN159": case "DYN160": case "DYN161": return "Aura";
    case "DYN162": case "DYN163": case "DYN164": return "Arrow";
    case "DYN165": case "DYN166": case "DYN167": return "Arrow";
    //Runeblade
    case "DYN171": return "Head";
    case "DYN172": return "Book";
    case "DYN175": return "Aura";
		case "DYN179": case "DYN180": case "DYN181": return "Aura";
    //Wizard
    case "DYN192": return "Staff";
    case "DYN193": return "Orb";
    case "DYN200": case "DYN201": case "DYN202": return "Aura";
    //Illusionist
    case "DYN212": return "Invocation";
    case "DYN213": return "Chest";
    case "DYN214": return "Arms";
    case "DYN217": return "Aura";
    case "DYN218": case "DYN219": case "DYN220": return "Aura";
    case "DYN221": case "DYN222": case "DYN223": return "Aura";
    //Generic
    case "DYN234": return "Head";
    case "DYN235": return "Off-Hand";
    case "DYN236": return "Head";
		case "DYN237": return "Chest";
		case "DYN238": return "Gloves";
		case "DYN239": return "Legs";
    case "DYN240": return "Item";
    case "DYN241": return "Item";
    case "DYN242": return "Item";
    case "DYN243": return "Item";
    case "DYN244": return "Aura";
		case "DYN246": return "Aura";
    case "DYN612": return "Angel,Ally";
    default:return "";
  }
}

function DYNPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
{
  global $currentPlayer, $CS_PlayIndex, $CS_NumContractsCompleted, $combatChainState, $CCS_NumBoosted, $CCS_CurrentAttackGainedGoAgain, $combatChain;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $rv = "";
  switch ($cardID) {
    case "DYN001":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKCARD,ARC159");
      AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("ATTACKWITHIT", $currentPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      return "";
    case "DYN007":
      if (AttackValue($additionalCosts) >= 6) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        $rv .= "Discarded a 6 power card and gains +" . EffectAttackModifier($cardID) . " power.";
      }
      return $rv;
    case "DYN009":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN002":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    case "DYN003":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    case "DYN004":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    case "DYN016": case "DYN017": case "DYN018":
      if (AttackValue($additionalCosts) >= 6) {
        $combatChainState[$CCS_CurrentAttackGainedGoAgain] = 1;
        $rv .= "Discarded a 6 power card and gains go again.";
      }
      return $rv;
    case "DYN019": case "DYN020": case "DYN021":
      if (AttackValue($additionalCosts) >= 6) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        $rv .= "Discarded a 6 power card and gains +" . EffectAttackModifier($cardID);
      }
      return $rv;
    case "DYN022": case "DYN023": case "DYN024":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $rv .= "Your next Brute attack this turn gains +" . EffectAttackModifier($cardID);
      return $rv;
    case "DYN025":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN028":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN030": case "DYN031": case "DYN032":
      if(!IsAllyAttacking()){
        $index = SearchCombatChainLink($currentPlayer, subtype:"Off-Hand", class:"GUARDIAN");
        if ($index != ""){
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Discard a card or PASS and take 1 damage");
          AddDecisionQueue("MAYCHOOSEHAND", $otherPlayer, "<-", 1);
          AddDecisionQueue("REMOVEMYHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("DISCARDCARD", $otherPlayer, "HAND", 1);
          AddDecisionQueue("PASSTAKEDAMAGE", $otherPlayer, 1);
        }
      }
      return "";
    case "DYN039":
    case "DYN040":
    case "DYN041":
      if ($cardID == "DYN039") $maxDef = 3;
      else if ($cardID == "DYN040") $maxDef = 2;
      else $maxDef = 1;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;subtype=Off-Hand;hasNegCounters=true;maxDef=" . $maxDef . ";class=GUARDIAN");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which Guardian Off-Hand to remove a -1 defense counter");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZGETCARDINDEX", $currentPlayer, "-", 1);
      AddDecisionQueue("REMOVENEGDEFCOUNTER", $currentPlayer, "-", 1);
      return "Remove a -1 counter from a Guardian Off-Hand with " . $maxDef . " or less base defense.";
    case "DYN042": case "DYN043": case "DYN044":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;subtype=Off-Hand;class=GUARDIAN");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which Guardian Off-Hand to buff", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      return "The next time you block with target guardian off-hand, it blocks for extra.";
    case "DYN046":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      return "Tearing Shuko gives your next Crouching Tiger +2.";
    case "DYN049":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddPlayerHand("DYN065", $currentPlayer, "-");
      return "Create a " . CardLink("DYN065","DYN065") . " in your hand and give them +1 this turn.";
    case "DYN062": case "DYN063": case "DYN064":
      if ($cardID == "DYN062") $amount = 3;
      else if ($cardID == "DYN063") $amount = 2;
      else $amount = 1;
      for ($i=0; $i < $amount; $i++) {
        BanishCardForPlayer("DYN065", $currentPlayer, "-", "TT", $currentPlayer);
      }
      return "Create " . $amount . " Crouching Tigers.";
    case "DYN068":
      if (isAttackGreaterThanTwiceBasePower()) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return " Attack is more than twice it's base power and gains overpower.";
      }
      return "";
    case "DYN071":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN072":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=W;subtype=Sword");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which Sword gain a +1 counter");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZGETCARDINDEX", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDEQUIPCOUNTER", $currentPlayer, "-", 1);
      return "Add a +1 counter from a sword you control.";
    case "DYN076": case "DYN077": case "DYN078":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "Your next sword or dagger attack gains go again and piercing.";
		case "DYN082": case "DYN083": case "DYN084":
      if ($cardID == "DYN082") $amount = 3;
      else if ($cardID == "DYN083") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "Your next axe attack this turn gains +" . $amount;
		case "DYN085": case "DYN086": case "DYN087":
      if ($cardID == "DYN085") $amount = 3;
      else if ($cardID == "DYN086") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "Your sword and dagger attacks have piercing " . $amount . " this turn.";
    case "DYN090":
      if(IsHeroAttackTarget() && $combatChainState[$CCS_NumBoosted] > 0)
      {
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $combatChainState[$CCS_NumBoosted]);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
        AddDecisionQueue("PREPENDLASTRESULT", $otherPlayer, "{0}-", 1);
        AddDecisionQueue("APPENDLASTRESULT", $otherPlayer, "-{0}", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose {0} card(s)", 1);
        AddDecisionQueue("MULTICHOOSEHAND", $otherPlayer, "<-", 1);
        AddDecisionQueue("IMPLODELASTRESULT", $otherPlayer, ",", 1);
        AddDecisionQueue("REVEALHANDCARDSRETURNLASTRESULT", $otherPlayer, "<-", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card", 1);
        AddDecisionQueue("PULSECHECKVALIDCARDS", $otherPlayer, $combatChainState[$CCS_NumBoosted], 1);
        AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("HANDCARD", $otherPlayer, "-", 1);
        AddDecisionQueue("BLOCKVALUE", $currentPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{0}", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
        AddDecisionQueue("ADDCARDTOCHAIN", $otherPlayer, "HAND", 1);
      }
      return "";
    case "DYN091":
      AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
      return "The next card you boost gets +3 attack and if you banish an item you play it.";
    case "DYN092":
      $hasHead = false; $hasChest = false; $hasArms = false; $hasLegs = false; $hasWeapon = false; $numHypers = 0;
      $char = &GetPlayerCharacter($currentPlayer);
      for($i=0; $i<count($char); $i+=CharacterPieces())
      {
        if($char[$i+1] == 0) continue;//If it's destroyed
        if(!ClassContains($char[$i], "MECHANOLOGIST", $currentPlayer)) continue;
        if(CardType($char[$i]) == "W") $hasWeapon = true;
        else {
          $subtype = CardSubType($char[$i]);
          switch($subtype)
          {
            case "Head": $hasHead = true; break;
            case "Chest": $hasChest = true; break;
            case "Arms": $hasArms = true; break;
            case "Legs": $hasLegs = true; break;
          }
        }
      }
      if(!$hasHead || !$hasChest || !$hasArms || !$hasLegs || !$hasWeapon) return "You do not meet the equipment requirement.";
      $numHypers = CountItem("ARC036", $currentPlayer);
      $numHypers += CountItem("DYN111", $currentPlayer);
      $numHypers += CountItem("DYN112", $currentPlayer);
      if($numHypers < 3) return "You do not meet the Hyper Driver requirement.";
      //Congrats, you have met the requirement to summon the mech! Let's remove the old stuff
      $mechMaterial = "";
      for($i=count($char)-1; $i>=CharacterPieces(); --$i) {
        if ($char[$i] == "DYN089") AddCurrentTurnEffect($char[$i] . "-UNDER", $currentPlayer);
        if($mechMaterial != "") $mechMaterial .= ",";
        $mechMaterial .= $char[$i];
        unset($char[$i]);
      }
      $char = array_values($char);
      $items = &GetItems($currentPlayer);
      $hyperToDestroy = 3;
      for($i=count($items)-ItemPieces(); $i>=0 && $hyperToDestroy>0; $i-=ItemPieces())
      {
        if($mechMaterial != "") $mechMaterial .= ",";
        $mechMaterial .= $items[$i];
        if($items[$i] == "ARC036" || $items[$i] == "DYN111" || $items[$i] == "DYN112") DestroyItemForPlayer($currentPlayer, $i);
        $hyperToDestroy--;
      }
      //Now add the new stuff
      PutCharacterIntoPlayForPlayer("DYN492a", $currentPlayer);//Weapon
      PutCharacterIntoPlayForPlayer("DYN492b", $currentPlayer);//Armor
      PutItemIntoPlayForPlayer("DYN492c", $currentPlayer);//Item
      return "";
    case "DYN095": case "DYN096": case "DYN097":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN119": case "DYN120": case "DYN122":
    case "DYN124": case "DYN125": case "DYN126":
    case "DYN127": case "DYN128": case "DYN129":
    case "DYN133": case "DYN134": case "DYN135":
    case "DYN136": case "DYN137": case "DYN138": //Contracts visualization
    case "DYN139": case "DYN140": case "DYN141":
    case "DYN142": case "DYN143": case "DYN144":
    case "DYN145": case "DYN146": case "DYN147":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN123":
      if (GetClassState($currentPlayer, $CS_NumContractsCompleted) > 0) {
        PutItemIntoPlayForPlayer("EVR195", $currentPlayer, 0, 4);
      }
      return "";
    case "DYN130": case "DYN131": case "DYN132":
      if ($cardID == "DYN130") $amount = 4;
      else if ($cardID == "DYN131") $amount = 3;
      else $amount = 2;
      $options = GetChainLinkCards(($currentPlayer == 1 ? 2 : 1), "", "C");
      if (!empty($options)) {
        AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
        AddDecisionQueue("COMBATCHAINDEBUFFDEFENSE", $currentPlayer, $amount, 1);
      }
      return "Reduce the defense of target defending card by " . $amount . ".";
    case "DYN148": case "DYN149": case "DYN150":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      AddDecisionQueue("DECKCARDS", $otherPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want sink <0> with Cut to the Chase", 1);
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_sink_the_opponent's_card", 1);
      AddDecisionQueue("NOPASSLOG", $currentPlayer, "Player" . $currentPlayer, 1);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "TOPDECK", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $otherPlayer, "<-", 1);
      AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
      return "";
    case "DYN151":
      $deck = &GetDeck($currentPlayer);
      AddDecisionQueue("DECKCARDS", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      if(ArsenalFull($currentPlayer)) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Sandscour Greatbow shows you the top of your deck: <0>");
        AddDecisionQueue("OK", $currentPlayer, "whether to put an arrow in arsenal", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "NO");
        return "Your arsenal is full, so you cannot put an arrow in your arsenal.";
      }
      if (CardSubType($deck[0]) != "Arrow") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Sandscour Greatbow shows you the top of your deck: <0>");
        AddDecisionQueue("OK", $currentPlayer, "whether to put an arrow in arsenal", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "NO");
      } else {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want to put <0> in your arsenal", 1);
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_put_the_card_in_arsenal", 1);
      }
      AddDecisionQueue("SANDSCOURGREATBOW", $currentPlayer, "-");
      return "";
    case "DYN155":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "Gives your next Arrow attack action card +" . EffectAttackModifier($cardID);
    case "DYN156": case "DYN157": case "DYN158":
      if (SearchCurrentTurnEffects("AIM", $currentPlayer)){
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "has piercing 1.";
      }
      return "";
    case "DYN165": case "DYN166": case "DYN167":
      if (SearchCurrentTurnEffects("AIM", $currentPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "has +2.";
      }
      return "";
    case "DYN168": case "DYN169": case "DYN170":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENALUP");
      AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDAIMCOUNTER", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDARSENALUNIQUEIDCURRENTEFFECT", $currentPlayer, $cardID . "," . "HAND", 1);
      return "";
    case "DYN171":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return CardLink("ARC112", "ARC112") . "s you control have spellvoid 1 this turn.";
    case "DYN172":
      $pitchArr = explode(",", $additionalCosts);
      $attackActionPitched = 0;
      $naaPitched = 0;
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "A") $naaPitched = 1;
        if (CardType($pitchArr[$i]) == "AA") $attackActionPitched = 1;
      }
      $rv = "Draw a card";
      MyDrawCard();
      if ($naaPitched && $attackActionPitched) {
        PlayAura("ARC112", $currentPlayer);
        $rv .= " and creates a Runechant token";
      }
      return $rv . ".";
    case "DYN173":
      $pitchArr = explode(",", $additionalCosts);
      $attackActionPitched = 0;
      $naaPitched = 0;
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "A") $naaPitched = 1;
        if (CardType($pitchArr[$i]) == "AA") $attackActionPitched = 1;
      }
      if ($naaPitched && $attackActionPitched && IsHeroAttackTarget()) {
        AddCurrentTurnEffect($cardID, $currentPlayer, $from);
        return "On-damage the defending hero discard a card and you draw a card";
      }
      return "";
    case "DYN174":
      $pitchArr = explode(",", $additionalCosts);
      $attackActionPitched = 0;
      $naaPitched = 0;
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "A") $naaPitched = 1;
        if (CardType($pitchArr[$i]) == "AA") $attackActionPitched = 1;
      }
      $rv = "";
      if ($attackActionPitched) {
        // Opponent
        AddDecisionQueue("FINDINDICES", $otherPlayer, "SEARCHMZ,MYALLY");
        AddDecisionQueue("CHOOSEMULTIZONE", $otherPlayer, "<-");
        AddDecisionQueue("MZDESTROY", $otherPlayer, "-", 1);
        // Player
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SEARCHMZ,MYALLY");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-");
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        $rv .= "Each hero chose and destroyed an ally they control.";
      }
      if ($naaPitched) {
        // Opponent
        AddDecisionQueue("FINDINDICES", $otherPlayer, "SEARCHMZ,MYAURAS");
        AddDecisionQueue("CHOOSEMULTIZONE", $otherPlayer, "<-");
        AddDecisionQueue("MZDESTROY", $otherPlayer, "-", 1);
        // Player
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SEARCHMZ,MYAURAS");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-");
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        if ($rv != "") $rv .= " ";
        $rv .= "Each hero chose and destroyed an aura they control.";
      }
      return $rv;
    case "DYN175":
      $numRunechants = DestroyAllThisAura($currentPlayer, "ARC112");
      $auras = &GetAuras($currentPlayer);
      $index = count($auras) - AuraPieces();//Get index of last played aura i.e. this
      $auras[$index+2] = $numRunechants;
      return "";
    case "DYN176": case "DYN177": case "DYN178":
      $pitchArr = explode(",", $additionalCosts);
      $attackActionPitched = 0;
      $naaPitched = 0;
      $rv = "";
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "A") $naaPitched = 1;
        if (CardType($pitchArr[$i]) == "AA") $attackActionPitched = 1;
      }
      if ($attackActionPitched) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        $rv .= "Gain +2 power";
      }
      if ($naaPitched) {
        PlayAura("ARC112", $currentPlayer, 2, true);
        if ($rv != "") $rv .= " and ";
        $rv .= "creates 2 Runechant.";
      }
      return $rv;
		case "DYN182": case "DYN183": case "DYN184":
      $pitchArr = explode(",", $additionalCosts);
      $naaPitched = 0;
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "A") $naaPitched = 1;
      }
      if ($naaPitched) {
        DealArcane(1, 2, "PLAYCARD", $cardID);
      }
      return "";
		case "DYN185": case "DYN186": case "DYN187":
      if ($cardID == "DYN185") $amount = 3;
      else if ($cardID == "DYN186") $amount = 2;
      else $amount = 1;
      $pitchArr = explode(",", $additionalCosts);
      $attackActionPitched = 0;
      $rv = "The next Runeblade attack action card you play creates " . $amount . " Runechant on-hit";
      for ($i = 0; $i < count($pitchArr); ++$i) {
        if (CardType($pitchArr[$i]) == "AA") $attackActionPitched = 1;
      }
      AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
      if ($attackActionPitched) {
        AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
        $rv .= " and gain +1 power";
      }
      return $rv . ".";
    case "DYN188": case "DYN189": case "DYN190":
      if (CanRevealCards($currentPlayer)) {
        $deck = GetDeck($currentPlayer);
        if (count($deck) == 0) return "Your deck is empty. Nothing was revealed.";
        if (PitchValue($deck[0]) == PitchValue($cardID)) {
          PlayAura("ARC112", $currentPlayer, 1, true);
          return "Reveals " . CardLink($deck[0], $deck[0]) . " and creates a " . CardLink("ARC112", "ARC112");
        } else {
          return "Reveals " . CardLink($deck[0], $deck[0]);
        }
      }
      return "Reveal has been prevented.";
    case "DYN192":
      DealArcane(1, 1, "ABILITY", $cardID, resolvedTarget: $target);
      AddDecisionQueue("SURGENTAETHERTIDE", $currentPlayer, "-");
      return "";
    case "DYN193":
      PlayerOpt($currentPlayer, 1, false);
      PlayAura("DYN244", $currentPlayer);
      return CardLink($cardID, $cardID) . " let you Opt 1 and create a Ponder token.";
  	case "DYN194":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "DYN195":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "DYN196":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN197": case "DYN198": case "DYN199":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "DYN203": case "DYN204": case "DYN205":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "DYN206": case "DYN207": case "DYN208":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "DYN209": case "DYN210": case "DYN211":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "DYN212":
      Transform($currentPlayer, "MON104", "DYN612");
      return "";
    case "DYN215":
      // TODO: Make named attack Illusionist
      return CardLink($cardID, $cardID) . " is a partially manual card. Name the card in chat and enforce play restriction.";
    case "DYN221": case "DYN222": case "DYN223":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      $auras = &GetAuras($currentPlayer);
      $uniqueID = $auras[count($auras) - AuraPieces() + 6];
      if($cardID == "DYN221") $maxCost = 3;
      else if($cardID == "DYN222") $maxCost = 2;
      else $maxCost = 1;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS:maxCost=" . $maxCost); // &LAYER:maxCost=1;type=AA
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "AURAS,DYN221-" . $uniqueID . "," . $currentPlayer, 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      return "";
    case "DYN224": case "DYN225": case "DYN226":
      if(SearchAuras("MON104", $currentPlayer))
      {
        GiveAttackGoAgain();
        return CardLink($cardID, $cardID) . " gains go again.";
      }
      return "";
    case "DYN227": case "DYN228": case "DYN229":
      if(SearchAuras("MON104", $currentPlayer))
      {
        AddCurrentTurnEffect("DYN227", $currentPlayer);
        return CardLink($cardID, $cardID) . " gains Overpower.";
      }
      return "";
    case "DYN230": case "DYN231": case "DYN232":
      if (CanRevealCards($currentPlayer)) {
        $deck = GetDeck($currentPlayer);
        if (count($deck) == 0) return "Your deck is empty. Nothing was revealed.";
        if (PitchValue($deck[0]) == PitchValue($cardID)) {
          PlayAura("MON104", $currentPlayer, 1, true);
          return "Reveals " . CardLink($deck[0], $deck[0]) . " and creates a " . CardLink("MON104", "MON104");
        } else {
          return "Reveals " . CardLink($deck[0], $deck[0]);
        }
      }
      return "Reveal has been prevented.";
    case "DYN235":
      BottomDeckDraw();
      return CardLink($cardID, $cardID) . " let you sink a card.";
    case "DYN240":
      $rv = "";
      if ($from == "PLAY") {
        DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
        $rv = CardLink($cardID, $cardID) . " is a partially manual card. Name the card in chat and enforce play restriction.";
        if(IsRoyal($currentPlayer))
        {
          $rv .= CardLink($cardID, $cardID) . " revealed the opponent's hand.";
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "<-", 1);
        }
      }
      return $rv;
    case "DYN241":
      $rv = "";
      if ($from == "PLAY") {
        DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
        $item = (IsRoyal($currentPlayer) ? "DYN243": "CRU197");
        PutItemIntoPlayForPlayer($item, $currentPlayer);
        $rv = CardLink($cardID, $cardID) . " shuffled itself and created a " . CardLink($item, $item) . ".";
        $deck = &GetDeck($currentPlayer);
        array_push($deck, "DYN241");
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      }
      return $rv;
    case "DYN242":
      $rv = "";
      if ($from == "PLAY") {
        DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose any number of heroes");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Both_Heroes,Target_Yourself,Target_No_Heroes");
        AddDecisionQueue("IMPERIALWARHORN", $currentPlayer, "<-", 1);
      }
      return $rv;
    case "DYN243":
      $rv = "";
      if ($from == "PLAY") {
        DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
        $rv = "Draws a card.";
        Draw($currentPlayer);
      }
      return $rv;
    case "DYN612":
      $mySoul = &GetSoul($currentPlayer);
      if (count($mySoul) > 0){
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SOUL");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish");
        AddDecisionQueue("MAYCHOOSEMYSOUL", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVESOUL", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "SOUL,-", 1);
        AddDecisionQueue("SURAYA", $currentPlayer, $cardID, 1);
      }
      return "";
    default:
      return "";
  }
}

function DYNHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_CurrentAttackGainedGoAgain, $CCS_DamageDealt, $CCS_NumBoosted;
  global $chainLinks, $combatChain;
  switch ($cardID) {
    case "DYN047":
      if (ComboActive())
      {
        $numLinks = 0;
        for ($i = 0; $i < count($chainLinks); ++$i) {
          if ($chainLinks[$i][0] == "DYN065") ++$numLinks;
        }
        if (count($combatChain) > 0 && $combatChain[0] == "DYN065") ++$numLinks;
        for ($i=0; $i < $numLinks; $i++) {
          BanishCardForPlayer("DYN065", $mainPlayer, "-", "TT", $mainPlayer);
        }
      }
      break;
    case "DYN050": case "DYN051": case "DYN052":
      BanishCardForPlayer("DYN065", $mainPlayer, "-", "TT", $mainPlayer);
      break;
    case "DYN067":
      if (IsHeroAttackTarget() && !SearchAuras("DYN246", $mainPlayer)) {
        PlayAura("DYN246", $mainPlayer);
      }
      break;
    case "DYN107": case "DYN108": case "DYN109":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYHAND:subtype=Item;class=MECHANOLOGIST;maxCost=" . $combatChainState[$CCS_NumBoosted]);
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to put into play");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
      AddDecisionQueue("MZGETCARDID", $mainPlayer, "-", 1);
      AddDecisionQueue("PUTPLAY", $mainPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $mainPlayer, "{0}", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      break;
    case "DYN115":
      if (IsHeroAttackTarget()) {
        AddCurrentTurnEffectFromCombat($cardID, $defPlayer);
      }
      break;
    case "DYN116":
      if (IsHeroAttackTarget()) {
        AddCurrentTurnEffectFromCombat($cardID, $defPlayer);
      }
      break;
    case "DYN117":
      if (IsHeroAttackTarget()) {
        $combatChainState[$CCS_CurrentAttackGainedGoAgain] = 1;
        WriteLog(CardLink($cardID, $cardID) . " gives the current Assassin attack go again.");
      }
      break;
    case "DYN118":
      if (IsHeroAttackTarget()) {
        $deck = &GetDeck($defPlayer);
        if (count($deck) == 0) WriteLog("The opponent deck is already... depleted.");
        $cardToBanish = array_shift($deck);
        BanishCardForPlayer($cardToBanish, $defPlayer, "DECK", "-", $mainPlayer);
        WriteLog(CardLink($cardToBanish, $cardToBanish) . " was banished.");
      }
      break;
    case "DYN119":
      if (IsHeroAttackTarget()) {
        $deck = &GetDeck($defPlayer);
        if (count($deck) == 0) { WriteLog("The opponent deck is already... depleted."); break; }
        $cardsName = "";
        for ($i = 0; $i < $combatChainState[$CCS_DamageDealt]; ++$i) {
          if (count($deck) == 0) break;
          $cardToBanish = array_shift($deck);
          BanishCardForPlayer($cardToBanish, $defPlayer, "DECK", "-", $mainPlayer);
          if ($cardsName != "") $cardsName .= ", ";
          $cardsName .= CardLink($cardToBanish, $cardToBanish);
        }
        if ($cardsName != "") WriteLog(CardLink($cardID, $cardID) . " Banished the following cards: " . $cardsName);
      }
      break;
    case "DYN121":
      if (IsHeroAttackTarget() && IsRoyal($defPlayer)) {
        PlayerLoseHealth($defPlayer, GetHealth($defPlayer));
      }
      break;
    case "DYN120":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("FINDINDICES", $mainPlayer, "SEARCHMZ,THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $mainPlayer, "ARS,-," . $mainPlayer, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        $deck = &GetDeck($defPlayer);
        if (count($deck) == 0) { WriteLog("The opponent deck is already... depleted."); break; }
        $cardToBanish = array_shift($deck);
        BanishCardForPlayer($cardToBanish, $defPlayer, "DECK", "-", $mainPlayer);
        WriteLog(CardLink($cardToBanish, $cardToBanish) . " was banished.");
      }
      break;
    case "DYN122":
      if (IsHeroAttackTarget()) {
        $deck = &GetDeck($defPlayer);
        if (count($deck) == 0) WriteLog("The opponent deck is already... depleted.");
        else {
          $cardToBanish = array_shift($deck);
          BanishCardForPlayer($cardToBanish, $defPlayer, "DECK", "-", $mainPlayer);
        }
        AddDecisionQueue("FINDINDICES", $mainPlayer, "SEARCHMZ,THEIRHAND", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $mainPlayer, "HAND,-," . $mainPlayer, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "DYN124": case "DYN125": case "DYN126":
    case "DYN127": case "DYN128": case "DYN129":
    case "DYN133": case "DYN134": case "DYN135":
    case "DYN136": case "DYN137": case "DYN138":
    case "DYN139": case "DYN140": case "DYN141":
    case "DYN142": case "DYN143": case "DYN144":
    case "DYN145": case "DYN146": case "DYN147":
      if (IsHeroAttackTarget()) {
        $deck = &GetDeck($defPlayer);
        if (count($deck) == 0) { WriteLog("The opponent deck is already... depleted."); break; }
        $cardToBanish = array_shift($deck);
        BanishCardForPlayer($cardToBanish, $defPlayer, "DECK", "-", $mainPlayer);
        WriteLog(CardLink($cardToBanish, $cardToBanish) . " was banished.");
      }
      break;
    case "DYN153":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    case "DYN154":
      if (SearchCurrentTurnEffects("AIM", $mainPlayer) && IsHeroAttackTarget()) {
        AddNextTurnEffect($cardID, $defPlayer);
        AddCurrentTurnEffectFromCombat($cardID . "-1", $defPlayer); //Doesn't do anything just show it in the effects
      }
      break;
    case "DYN156": case "DYN157": case "DYN158":
      if (IsHeroAttackTarget()){
        AddDecisionQueue("FINDINDICES", $defPlayer, "EQUIP");
        AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
        AddDecisionQueue("ADDNEGDEFCOUNTER", $defPlayer, "-", 1);
      }
      break;
    case "DYN162": case "DYN163": case "DYN164":
      if (SearchCurrentTurnEffects("AIM", $mainPlayer) && IsHeroAttackTarget()) {
        AddDecisionQueue("FINDINDICES", $mainPlayer, "SEARCHMZ,THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to Destroy", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDISCARD", $mainPlayer, "ARS,-," . $mainPlayer, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    default: break;
  }
}

function IsRoyal($player)
{
  $mainCharacter = &GetPlayerCharacter($player);

  if (SearchCharacterForCard($player, "DYN234")) return true;

  switch ($mainCharacter[0]) {
    case "DYN001": return true;
    default: break;
  }
  return false;
}

function HasSurge($cardID)
{
  switch ($cardID) {
		case "DYN194": return true;
		case "DYN195": return true;
    case "DYN197": case "DYN198": case "DYN199": return true;
    case "DYN203": case "DYN204": case "DYN205": return true;
    case "DYN206": case "DYN207": case "DYN208": return true;
    default: return false;
  }
}

function HasEphemeral($cardID)
{
  switch ($cardID) {
    case "DYN065": return true;
    default: return false;
  }
}

function ContractType($cardID)
{
  switch($cardID)
  {
    case "DYN119": return "YELLOWPITCH";
    case "DYN120": return "REDPITCH";
    case "DYN122": return "BLUEPITCH";
    case "DYN124": case "DYN125": case "DYN126": return "COST1ORLESS";
    case "DYN127": case "DYN128": case "DYN129": return "COST2ORMORE";
    case "DYN133": case "DYN134": case "DYN135": return "AA";
    case "DYN136": case "DYN137": case "DYN138": return "BLOCK2ORLESS";
    case "DYN139": case "DYN140": case "DYN141": return "REACTIONS";
    case "DYN142": case "DYN143": case "DYN144": return "GOAGAIN";
    case "DYN145": case "DYN146": case "DYN147": return "NAA";
    default: return "";
  }
}

function ContractCompleted($player, $cardID)
{
  global $CS_NumContractsCompleted;
  WriteLog("Player " . $player . " completed the contract for " . CardLink($cardID, $cardID) . ".");
  IncrementClassState($player, $CS_NumContractsCompleted);
  switch($cardID)
  {
    case "DYN119": case "DYN120": case "DYN122":
    case "DYN124": case "DYN125": case "DYN126":
    case "DYN127": case "DYN128": case "DYN129":
    case "DYN133": case "DYN134": case "DYN135":
    case "DYN136": case "DYN137": case "DYN138":
    case "DYN139": case "DYN140": case "DYN141":
    case "DYN142": case "DYN143": case "DYN144":
    case "DYN145": case "DYN146": case "DYN147":
      PutItemIntoPlayForPlayer("EVR195", $player);
      break;
    default: break;
  }
}

function CheckContracts($banishedBy, $cardBanished)
{
  global $combatChain, $chainLinks;
  //Current Chainlink
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    if ($combatChain[$i + 1] != $banishedBy) continue;
    $contractType = ContractType($combatChain[$i]);
    $contractCompleted = false;
    switch ($contractType) {
      case "REDPITCH":
        if (PitchValue($cardBanished) == 1) $contractCompleted = true;
        break;
      case "YELLOWPITCH":
        if (PitchValue($cardBanished) == 2) $contractCompleted = true;
        break;
      case "BLUEPITCH":
        if (PitchValue($cardBanished) == 3) $contractCompleted = true;
        break;
      case "COST1ORLESS":
        if (CardCost($cardBanished) <= 1) $contractCompleted = true;
        break;
      case "COST2ORMORE":
        if (CardCost($cardBanished) >= 2) $contractCompleted = true;
        break;
      case "AA":
        if (CardType($cardBanished) == "AA") $contractCompleted = true;
        break;
      case "GOAGAIN":
        if (HasGoAgain($cardBanished)) $contractCompleted = true;
        break;
      case "NAA":
        if (CardType($cardBanished) == "A") $contractCompleted = true;
        break;
      case "BLOCK2ORLESS":
        if (BlockValue($cardBanished) <= 2 && BlockValue($cardBanished) >= 0) $contractCompleted = true;
        break;
      case "REACTIONS":
        if (CardType($cardBanished) == "AR" || CardType($cardBanished) == "DR") $contractCompleted = true;
        break;
      default:
        break;
    }
    if ($contractCompleted) ContractCompleted($banishedBy, $combatChain[$i]);
  }
  //Chain Links
  for ($i = 0; $i < count($chainLinks); ++$i) {
    for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if ($chainLinks[$i][$j + 1] != $banishedBy) continue;
      if ($chainLinks[$i][$j + 2] == 0) continue; //Skip if the card isn't on the chain anymore
      $contractType = ContractType($chainLinks[$i][$j]);
      $contractCompleted = false;
      switch ($contractType) {
        case "REDPITCH":
          if (PitchValue($cardBanished) == 1) $contractCompleted = true;
          break;
        case "YELLOWPITCH":
          if (PitchValue($cardBanished) == 2) $contractCompleted = true;
          break;
        case "BLUEPITCH":
          if (PitchValue($cardBanished) == 3) $contractCompleted = true;
          break;
        case "COST1ORLESS":
          if (CardCost($cardBanished) <= 1) $contractCompleted = true;
          break;
        case "COST2ORMORE":
          if (CardCost($cardBanished) >= 2) $contractCompleted = true;
          break;
        case "AA":
          if (CardType($cardBanished) == "AA") $contractCompleted = true;
          break;
        case "GOAGAIN":
          if (HasGoAgain($cardBanished)) $contractCompleted = true;
          break;
        case "NAA":
          if (CardType($cardBanished) == "A") $contractCompleted = true;
          break;
        case "BLOCK2ORLESS":
          if (BlockValue($cardBanished) <= 2 && BlockValue($cardBanished) >= 0) $contractCompleted = true;
          break;
        case "REACTIONS":
          if (CardType($cardBanished) == "AR" || CardType($cardBanished) == "DR") $contractCompleted = true;
          break;
        default:
          break;
        }
      if ($contractCompleted) ContractCompleted($banishedBy, $chainLinks[$i][$j]);
      }
    }
}
