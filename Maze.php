<?php

class MazeSolver
{
	private $fileName;		
	private $maze;
	private $mazeSize;
			
	private $startX;
	private $startY;
			
	private $endX;
	private $endY;
			
	private $currentX;
	private $currentY;			
			
	private $intersectionX = [];
	private $intersectionY = [];
	private $pathsCount;
	private $pathX = [];
	private $pathY = [];
			
	private $visitedX = [];
	private $visitedY = [];
	private $visited;
			
	private $possibleDirections = "";
	private $nextDirection;
			
	private $atStart;
	private $finished;
			
	private $pink;
			
	private $splitIndex;
			
			
	function __construct($fileName) {
		//makes the maze
		$this->fileName = $fileName;
		$this->maze = imagecreatefrompng($fileName);
		$this->mazeSize = getimagesize($fileName);
		//sets the y axis
		$this->startY = 9;
		$this->pathY = [9];
		$this->visitedY = [9];
		$this->endY = $this->mazeSize[0]-9;
		$this->currentY = 9;
		
		$this->pathsCount = 0;
		$this->visited = false;
		$this->atStart = true;
		$this->finished = false;
		
		$this->pink = imagecolorallocate($this->maze, 255, 16, 240);
		
		//finds and sets start, current, path, and visited X coords
		for ($i = 0;$i <= $this->mazeSize[0];$i++){
			if (imagecolorat($this->maze, $i, 0) !== 0){
				
				$this->startX = $i + 6;
				$this->currentX = $i + 6;
				$this->pathX = [$i + 6];
				$this->visitedX = [$i + 6];						
				break;
			}
		}
			
		//finds and sets end position
		for ($i = 0;$i <= $this->mazeSize[0];$i++){
			if (imagecolorat($this->maze, $i , $this->mazeSize[0]-1) !== 0){
				
				return $this->endX = $i + 6;						
			}
								
		}														
	}
	
	//finds coords for route to end
	public function getPath() {
		do {
						
			$this->findDirection();

			$this->move();


			//making a $finished variable was probably unecessary, however when i had the if statement as part of the while it didn't work
			if ($this->currentX === $this->endX && $this->currentY === $this->endY){
				$this->finished = true;
			}
					
		} while (!$this->finished);
		
		$this->drawLine();

		ob_clean();
		header("Content-type: image/png");
		imagepng($this->maze);
				
	}
	
	//finds all possible directions from current position, also add and removes intersections when necessary
	public function findDirection() {
		//checks to see if at start
		if ($this->currentX !== $this->startX && $this->currentY !== $this->startY){
					
			$this->atStart = false;
		}
					
		if ($this->currentX === $this->startX && $this->currentY === $this->startY){
					
			$this->atStart = true;
		}
				
		//checks up
		if (imagecolorat($this->maze, $this->currentX, $this->currentY - 8) !== 0){
					
			if (!$this->atStart){
					
				//checks to see if it's been to that section before
				for ($i = 0; $i < count($this->visitedY); $i++){
							
					if ($this->visitedY[$i] === $this->currentY - 16 &&
						$this->visitedX[$i] === $this->currentX){
									
						$this->visited = true;
					}
				}
							
				if (!$this->visited){
												
					$this->pathsCount += 1;
					$this->possibleDirections .= "U";
				} else {
					$this->visited = false;
				}
			}	
		}
					
		//checks right
		if (imagecolorat($this->maze, $this->currentX + 8, $this->currentY) !== 0){
						
			for ($i = 0; $i < count($this->visitedX); $i++){
						
				if ($this->visitedX[$i] === $this->currentX + 16 &&
					$this->visitedY[$i] === $this->currentY){
								
					$this->visited = true;
				}
			}
					
					
			if (!$this->visited){
						
				$this->pathsCount += 1;
				$this->possibleDirections .= "R";
			} else {
				$this->visited = false;
			}
		}
					
		//checks down
		if (imagecolorat($this->maze, $this->currentX, $this->currentY + 7) !== 0){
						
			for ($i = 0; $i < count($this->visitedY); $i++){
						
				if ($this->visitedY[$i] === $this->currentY + 16 &&
					$this->visitedX[$i] === $this->currentX){
								
					$this->visited = true;
				}
			}	
						
			if (!$this->visited){	
					
				$this->pathsCount += 1;
				$this->possibleDirections .= "D";
			} else {
				$this->visited = false;
			}
		}
				
		//checks left
		if (imagecolorat($this->maze, $this->currentX - 7, $this->currentY) !== 0){
						
			for ($i = 0; $i < count($this->visitedX); $i++){
						
				if ($this->visitedX[$i] === $this->currentX - 16 &&
					$this->visitedY[$i] === $this->currentY){
								
					$this->visited = true;
				}
			}
						
			if (!$this->visited){	
					
				$this->pathsCount += 1;	
				$this->possibleDirections .= "L";
			} else {
				$this->visited = false;
			}
		}
				
				
		
		//if multiple paths are found their coords are added to intersection arrays
		if ($this->pathsCount > 1){
			
			$this->intersectionX[] = $this->currentX;
			$this->intersectionY[] = $this->currentY;
		}
		
		//moves back to last intersection if at a deadend and removes deadend path from path arrays
		if ($this->pathsCount === 0) {
			
			for ($i = count($this->pathX); $i >= 0; $i--) {

				//if we are an intersection break
				if (end($this->pathX) === end($this->intersectionX) &&
					end($this->pathY) === end($this->intersectionY)) {

					break;
				}

				array_pop($this->pathX);
				array_pop($this->pathY);
			}

			$this->currentX = end($this->intersectionX);
			$this->currentY = end($this->intersectionY);
		}				
				
		//removes coords from intersection arrays if it went down all paths but one
		if ($this->pathsCount === 1){
						
			if (end($this->intersectionX) === $this->currentX &&
				end($this->intersectionY) === $this->currentY){
								
				array_pop($this->intersectionX);
				array_pop($this->intersectionY);
			}
											
		}
		$this->pathsCount = 0;	
	}
	
	//moves		
	public function move() {
		//exits function if their are no directions to go in
		if (empty($this->possibleDirections)){
					
			return;
		}
				
		$this->nextDirection = str_split($this->possibleDirections)[0];
				
		//moves up
		if ($this->nextDirection === "U"){
					
			$this->currentY -= 16;					
		}
				
		//moves right
		if ($this->nextDirection === "R"){
					
			$this->currentX += 16;
		}
				
		//moves down
		if ($this->nextDirection === "D"){
					
			$this->currentY += 16;
		}
				
		//moves left
		if ($this->nextDirection === "L"){
					
			$this->currentX -= 16;					
		}
				
		$this->possibleDirections = "";
		array_push($this->visitedX, $this->currentX);
		array_push($this->visitedY, $this->currentY);
		array_push($this->pathX, $this->currentX);
		array_push($this->pathY, $this->currentY);
	}
	
	//draws line to end
	public function drawLine() {
		
		for ($i = 1; $i < count($this->pathX); $i++){
			imageline($this->maze, $this->pathX[$i-1], $this->pathY[$i-1], $this->pathX[$i], $this->pathY[$i], $this->pink);
		}												
	}
}

$solvedMaze = new MazeSolver("70.png");
			
$solvedMaze->getPath();	
