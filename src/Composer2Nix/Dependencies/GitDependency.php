<?php
namespace Composer2Nix\Dependencies;
use PNDP\NixGenerator;
use PNDP\AST\NixExpression;
use PNDP\AST\NixFunInvocation;

/**
 * Represents a Git dependency.
 */
class GitDependency extends Dependency
{
	/**
	 * Constructs a new Git dependency instance.
	 *
	 * @param array $package An array of package configuration properties
	 * @param array $sourceObj An array of download properties
	 */
	public function __construct(array $package, array $sourceObj)
	{
		parent::__construct($package, $sourceObj);
	}

	/**
	 * @see NixAST::toNixAST
	 */
	public function toNixAST()
	{
		$dependency = parent::toNixAST();

		$outputStr = shell_exec('nix-prefetch-git "'.$this->sourceObj['url'].'" '.$this->sourceObj["reference"]);

		if($outputStr === false)
			throw new Exception("Error while invoking nix-prefetch-git");
		else
		{
			$output = json_decode($outputStr, true);
			$hash = $output["sha256"];

			$dependency["src"] = new NixFunInvocation(new NixExpression("fetchgit"), array(
				"name" => strtr($this->package["name"], "/", "-").'-'.$this->sourceObj["reference"],
				"url" => $this->sourceObj["url"],
				"rev" => $this->sourceObj["reference"],
				"sha256" => $hash
			));
		}

		return $dependency;
	}
}
?>
